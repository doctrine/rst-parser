<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\HTML\Kernel;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Nodes\TableNode;
use function array_pop;
use function count;
use function explode;
use function file_get_contents;
use function implode;
use function in_array;
use function is_readable;
use function preg_match;
use function preg_replace_callback;
use function realpath;
use function sprintf;
use function str_replace;
use function strlen;
use function strpos;
use function substr;
use function trim;

class Parser
{
    public const STATE_BEGIN     = 0;
    public const STATE_NORMAL    = 1;
    public const STATE_DIRECTIVE = 2;
    public const STATE_BLOCK     = 3;
    public const STATE_TITLE     = 4;
    public const STATE_LIST      = 5;
    public const STATE_SEPARATOR = 6;
    public const STATE_CODE      = 7;
    public const STATE_TABLE     = 8;
    public const STATE_COMMENT   = 9;

    /** @var Kernel */
    protected $kernel;

    /** @var int */
    protected $state;

    /** @var Document */
    protected $document;

    /** @var TableNode|ListNode|string[] */
    protected $buffer = [];

    /** @var null|string */
    protected $specialLetter;

    /** @var false|null|string[] */
    protected $directive = false;

    /** @var Directive[] */
    protected $directives = [];

    /** @var null|Environment */
    protected $environment = null;

    /** @var bool */
    protected $includeAllowed = true;

    /** @var string */
    protected $includeRoot = '';

    /** @var bool */
    protected $isCode = false;

    /** @var int */
    protected $currentLine = 0;

    /** @var null|string */
    protected $filename = null;

    /** @var null|false|string[] */
    protected $lineInfo;

    /** @var null|false|string[] */
    protected $listLine;

    /** @var bool */
    protected $listFlow = false;

    public function __construct(?Environment $environment = null, ?Kernel $kernel = null)
    {
        if ($kernel === null) {
            $kernel = new Kernel();
        }
        $this->kernel = $kernel;

        $this->environment = $environment ?: $this->kernel->build('Environment');

        $this->initDirectives();
        $this->initReferences();
    }

    public function getSubParser() : Parser
    {
        return new Parser($this->environment, $this->kernel);
    }

    public function parseLink(string $line) : bool
    {
        // Links
        if (preg_match('/^\.\. _`(.+)`: (.+)$/mUsi', $line, $match)) {
            $this->environment->setLink($match[1], $match[2]);

            return true;
        }

        if (preg_match('/^\.\. _(.+): (.+)$/mUsi', $line, $match)) {
            $this->environment->setLink($match[1], $match[2]);

            return true;
        }

        // Short anonymous links
        if (preg_match('/^__ (.+)$/mUsi', trim($line), $match)) {
            $url = $match[1];

            $this->environment->setLink('_', $url);

            return true;
        }

        // Anchor link
        if (preg_match('/^\.\. _(.+):$/mUsi', trim($line), $match)) {
            $anchor = $match[1];

            $this->document->addNode($this->kernel->build('Nodes\AnchorNode', $anchor));
            $this->environment->setLink($anchor, '#' . $anchor);

            return true;
        }

        return false;
    }

    public function initDirectives() : void
    {
        $directives = $this->kernel->getDirectives();

        foreach ($directives as $name => $directive) {
            $this->registerDirective($directive);
        }
    }

    public function initReferences() : void
    {
        $references = $this->kernel->getReferences();

        foreach ($references as $reference) {
            $this->environment->registerReference($reference);
        }
    }

    public function getEnvironment() : Environment
    {
        return $this->environment;
    }

    public function getKernel() : Kernel
    {
        return $this->kernel;
    }

    public function registerDirective(Directive $directive) : void
    {
        $this->directives[$directive->getName()] = $directive;
    }

    protected function prepareCode() : bool
    {
        if ($this->buffer === []) {
            return false;
        }

        $lastLine = trim($this->buffer[count($this->buffer)-1]);

        if (strlen($lastLine) >= 2) {
            if (substr($lastLine, -2) === '::') {
                if (trim($lastLine) === '::') {
                    array_pop($this->buffer);
                } else {
                    $this->buffer[count($this->buffer)-1] = substr($lastLine, 0, -1);
                }

                return true;
            }
        }

        return false;
    }

    protected function init() : void
    {
        $this->specialLetter = false;
        $this->buffer        = [];
    }

    protected function isSpecialLine(string $line) : ?string
    {
        if (strlen($line) < 3) {
            return null;
        }

        $letter = $line[0];

        $environment = $this->environment;
        if (! in_array($letter, $environment::$letters, true)) {
            return null;
        }

        for ($i=1; $i<strlen($line); $i++) {
            if ($line[$i] !== $letter) {
                return null;
            }
        }

        return $letter;
    }

    /**
     * @return string[]
     */
    protected function findTableChars(string $line) : ?array
    {
        $lineChar  = $line[0];
        $spaceChar = null;

        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] === $lineChar) {
                continue;
            }

            if ($spaceChar === null) {
                $spaceChar = $line[$i];
            } else {
                if ($line[$i] !== $spaceChar) {
                    return null;
                }
            }
        }

        return [$lineChar, $spaceChar];
    }

    /**
     * @return null|string[]
     */
    protected function parseTableLine(string $line) : ?array
    {
        $header = false;
        $pretty = false;
        $line   = trim($line);

        if ($line === '') {
            return null;
        }

        // Finds the table chars
        $chars = $this->findTableChars($line);

        if ($chars === null) {
            return null;
        }

        if ($chars[0] === Environment::$prettyTableJoint && $chars[1] === Environment::$prettyTableLetter) {
            $pretty = true;
            $chars  = [Environment::$prettyTableLetter, Environment::$prettyTableJoint];
        } elseif ($chars[0] === Environment::$prettyTableJoint && $chars[1] === Environment::$prettyTableHeader) {
            $pretty = true;
            $header = true;
            $chars  = [Environment::$prettyTableHeader, Environment::$prettyTableJoint];
        } else {
            if (! ($chars[0] === Environment::$tableLetter && $chars[1] === ' ')) {
                return null;
            }
        }

        $parts     = [];
        $separator = false;

        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] === $chars[0]) {
                if (! $separator) {
                    $parts[]   = $i;
                    $separator = true;
                }
            } else {
                if ($line[$i] !== $chars[1]) {
                    return null;
                }

                $separator = false;
            }
        }

        if (count($parts) > 1) {
            return [
                $header,
                $pretty,
                $parts,
            ];
        }

        return null;
    }

    /**
     * @return null|string[]
     */
    protected function parseListLine(string $line) : ?array
    {
        $depth = 0;
        $i     = 0;

        for ($i = 0; $i < strlen($line); $i++) {
            $char = $line[$i];

            if ($char === ' ') {
                $depth++;
            } elseif ($char === "\t") {
                $depth += 2;
            } else {
                break;
            }
        }

        if (preg_match('/^((\*|\-)|([\d#]+)\.) (.+)$/', trim($line), $match)) {
            return [
                'prefix' => $line[$i],
                'ordered' => ($line[$i] === '*' || $line[$i] === '-') ? false : true,
                'depth' => $depth,
                'text' => [$match[4]],
            ];
        } elseif (strlen($line) === 1 && $line[0] === '-') {
            return [
                'prefix' => $line[$i],
                'ordered' => ($line[$i] === '*' || $line[$i] === '-') ? false : true,
                'depth' => $depth,
                'text' => [''],
            ];
        }

        return null;
    }

    protected function isListLine(string $line) : bool
    {
        $listLine = $this->parseListLine($line);

        if ($listLine !== null) {
            return $listLine['depth'] === 0 || ! $this->isCode;
        }

        return false;
    }

    public function pushListLine(?string $line, bool $flush = false) : bool
    {
        if ($line !== null && trim($line) !== '') {
            $infos = $this->parseListLine($line);

            if ($infos !== null) {
                if ($this->lineInfo) {
                    $this->lineInfo['text'] = $this->createSpan($this->lineInfo['text']);
                    $this->buffer->addLine($this->lineInfo);
                }
                $this->lineInfo = $infos;
            } else {
                if ($this->listFlow || $line[0] === ' ') {
                    $this->lineInfo['text'][] = $line;
                } else {
                    $flush = true;
                }
            }
            $this->listFlow = true;
        } else {
            $this->listFlow = false;
        }

        if ($flush) {
            if ($this->lineInfo) {
                $this->lineInfo['text'] = $this->createSpan($this->lineInfo['text']);
                $this->buffer->addLine($this->lineInfo);
                $this->lineInfo = null;
            }

            return false;
        }

        return true;
    }

    protected function isBlockLine(string $line) : bool
    {
        if ($line !== '') {
            return trim($line[0]) === '';
        }

        return trim($line) === '';
    }

    protected function initDirective(string $line) : bool
    {
        if (preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::( (.*)|)$/mUsi', $line, $match)) {
            $this->directive = [
                'variable' => $match[2],
                'name' => $match[3],
                'data' => trim($match[4]),
                'options' => [],
            ];

            return true;
        }

        return false;
    }

    protected function isComment(string $line) : bool
    {
        return preg_match('/^\.\. (.*)$/mUsi', $line) > 0;
    }

    protected function isDirective(string $line) : bool
    {
        return preg_match('/^\.\. (\|(.+)\| |)([^\s]+)::(.*)$/mUsi', $line) > 0;
    }

    protected function directiveAddOption(string $line) : bool
    {
        if (preg_match('/^(\s+):(.+): (.*)$/mUsi', $line, $match)) {
            $this->directive['options'][$match[2]] = trim($match[3]);

            return true;
        }

        if (preg_match('/^(\s+):(.+):(\s*)$/mUsi', $line, $match)) {
            $value = trim($match[3]);

            $this->directive['options'][$match[2]] = true;

            return true;
        }

        return false;
    }

    protected function getCurrentDirective() : ?Directive
    {
        if (! $this->directive) {
            $this->getEnvironment()->getErrorManager()->error('Asking for current directive, but there is not');
        }

        $name = $this->directive['name'];

        if (isset($this->directives[$name])) {
            return $this->directives[$name];
        }

        $message  = 'Unknown directive: ' . $name;
        $message .= ' in ' . $this->getFilename() . ' line ' . $this->getCurrentLine();

        $this->getEnvironment()->getErrorManager()->error($message);

        return null;
    }

    protected function flush() : void
    {
        $node = null;

        $this->isCode = false;

        if ($this->buffer !== []) {
            switch ($this->state) {
                case self::STATE_TITLE:
                    $data  = implode("\n", $this->buffer);
                    $level = $this->environment->getLevel($this->specialLetter);
                    $token = $this->environment->createTitle($level);
                    $node  = $this->kernel->build('Nodes\TitleNode', $this->createSpan($data), $level, $token);
                    break;
                case self::STATE_SEPARATOR:
                    $level = $this->environment->getLevel($this->specialLetter);
                    $node  = $this->kernel->build('Nodes\SeparatorNode', $level);
                    break;
                case self::STATE_CODE:
                    $node = $this->kernel->build('Nodes\CodeNode', $this->buffer);
                    break;
                case self::STATE_BLOCK:
                    /** @var QuoteNode $node */
                    $node      = $this->kernel->build('Nodes\QuoteNode', $this->buffer);
                    $data      = $node->getValue();
                    $subParser = $this->getSubParser();
                    $document  = $subParser->parseLocal($data);
                    $node->setValue($document);
                    break;
                case self::STATE_LIST:
                    $this->pushListLine(null, true);
                    $node = $this->buffer;
                    break;
                case self::STATE_TABLE:
                    $node = $this->buffer;
                    $node->finalize($this);
                    break;
                case self::STATE_NORMAL:
                    $this->isCode = $this->prepareCode();
                    $node         = $this->kernel->build('Nodes\ParagraphNode', $this->createSpan($this->buffer));
                    break;
            }
        }

        if ($this->directive) {
            $currentDirective = $this->getCurrentDirective();

            if ($currentDirective !== null) {
                $currentDirective->process(
                    $this,
                    $node,
                    $this->directive['variable'],
                    $this->directive['data'],
                    $this->directive['options']
                );
            }
            $node = null;
        }

        $this->directive = null;

        if ($node !== null) {
            $this->document->addNode($node);
        }

        $this->init();
    }

    public function getDocument() : Document
    {
        return $this->document;
    }

    protected function parseLine(string &$line) : bool
    {
        switch ($this->state) {
            case self::STATE_BEGIN:
                if (trim($line) !== '') {
                    if ($this->isListLine($line)) {
                        $this->state    = self::STATE_LIST;
                        $this->buffer   = $this->kernel->build('Nodes\ListNode');
                        $this->lineInfo = null;
                        $this->listFlow = true;
                        return false;
                    } elseif ($this->isBlockLine($line)) {
                        if ($this->isCode) {
                            $this->state = self::STATE_CODE;
                        } else {
                            $this->state = self::STATE_BLOCK;
                        }
                        return false;
                    } elseif ($this->isDirective($line)) {
                        $this->state  = self::STATE_DIRECTIVE;
                        $this->buffer = [];
                        $this->flush();
                        $this->initDirective($line);
                    } elseif ($this->parseLink($line)) {
                        return true;
                    } else {
                        $tableParts = $this->parseTableLine($line);

                        if ($tableParts === null) {
                            $this->state = self::STATE_NORMAL;

                            return false;
                        }

                        $this->state  = self::STATE_TABLE;
                        $this->buffer = $this->kernel->build('Nodes\TableNode', $tableParts);
                    }
                }
                break;

            case self::STATE_LIST:
                if (! $this->pushListLine($line)) {
                    $this->flush();
                    $this->state = self::STATE_BEGIN;
                    return false;
                }
                break;

            case self::STATE_TABLE:
                if (trim($line) === '') {
                    $this->flush();
                    $this->state = self::STATE_BEGIN;
                } else {
                    $parts = $this->parseTableLine($line);

                    if (! $this->buffer->push($parts, $line)) {
                        $this->flush();
                        $this->state = self::STATE_BEGIN;
                        return false;
                    }
                }

                break;

            case self::STATE_NORMAL:
                if (trim($line) !== '') {
                    $specialLetter = $this->isSpecialLine($line);

                    if ($specialLetter !== null) {
                        $this->specialLetter = $specialLetter;
                        $lastLine            = array_pop($this->buffer);

                        if ($lastLine !== null) {
                            $this->buffer = [$lastLine];
                            $this->state  = self::STATE_TITLE;
                        } else {
                            $this->buffer[] = $line;
                            $this->state    = self::STATE_SEPARATOR;
                        }
                        $this->flush();
                        $this->state = self::STATE_BEGIN;
                    } else {
                        if ($this->isDirective($line)) {
                            $this->flush();
                            $this->state = self::STATE_BEGIN;
                            return false;
                        }
                        if ($this->isComment($line)) {
                            $this->flush();
                            $this->state = self::STATE_COMMENT;
                        } else {
                            $this->buffer[] = $line;
                        }
                    }
                } else {
                    $this->flush();
                    $this->state = self::STATE_BEGIN;
                }
                break;

            case self::STATE_COMMENT:
                $isComment = false;

                if (! $this->isComment($line) && (trim($line) === '' || $line[0] !== ' ')) {
                    $this->state = self::STATE_BEGIN;
                    return false;
                }
                break;

            case self::STATE_BLOCK:
            case self::STATE_CODE:
                if (! $this->isBlockLine($line)) {
                    $this->flush();
                    $this->state = self::STATE_BEGIN;
                    return false;
                } else {
                    $this->buffer[] = $line;
                }
                break;

            case self::STATE_DIRECTIVE:
                if (! $this->directiveAddOption($line)) {
                    if (! $this->isDirective($line)) {
                        $directive    = $this->getCurrentDirective();
                        $this->isCode = $directive !== null ? $directive->wantCode() : false;
                        $this->state  = self::STATE_BEGIN;
                        return false;
                    }

                    $this->flush();
                    $this->initDirective($line);
                }
                break;

            default:
                $this->getEnvironment()->getErrorManager()->error('Parser ended in an unexcepted state');
        }

        return true;
    }

    public function includeFileAllowed(string $path) : bool
    {
        if (! $this->includeAllowed) {
            return false;
        }

        if (! @is_readable($path)) {
            return false;
        }

        if ($this->includeRoot === '') {
            return true;
        }

        $real = realpath($path);

        foreach (explode(':', $this->includeRoot) as $root) {
            if (strpos($real, $root) === 0) {
                return true;
            }
        }

        return false;
    }

    public function includeFiles(string $document) : string
    {
        $environment = $this->getEnvironment();
        $parser      = $this;

        return preg_replace_callback('/^\.\. include:: (.+)$/m', function ($match) use ($parser, $environment) {
            $path = $environment->absoluteRelativePath($match[1]);
            if ($parser->includeFileAllowed($path)) {
                return $parser->includeFiles(file_get_contents($path));
            }

            return '';
        }, $document);
    }

    protected function parseLines(string $document) : void
    {
        // Including files
        $document = str_replace("\r\n", "\n", $document);
        $document = sprintf("\n%s\n", $document);
        $document = $this->includeFiles($document);

        // Removing UTF-8 BOM
        $bom      = "\xef\xbb\xbf";
        $document = str_replace($bom, '', $document);

        $lines       = explode("\n", $document);
        $this->state = self::STATE_BEGIN;

        foreach ($lines as $n => $line) {
            $this->currentLine = $n;

            while (true) {
                if ($this->parseLine($line)) {
                    break;
                }
            }
        }

        // Document is flushed twice to trigger the directives
        $this->flush();
        $this->flush();
    }

    public function parse(string $document) : Document
    {
        $this->getEnvironment()->reset();

        return $this->parseLocal($document);
    }

    public function parseLocal(string $document) : Document
    {
        $this->document = $this->kernel->build('Document', $this->environment);

        $this->init();

        $this->parseLines(trim($document));

        foreach ($this->directives as $name => $directive) {
            $directive->finalize($this->document);
        }

        return $this->document;
    }

    public function parseFile(string $file) : Document
    {
        $this->filename = $file;

        return $this->parse(file_get_contents($file));
    }

    public function getFilename() : string
    {
        return $this->filename ?: '(unknown)';
    }

    public function getCurrentLine() : int
    {
        return $this->currentLine;
    }

    /**
     * @param string|string[] $span
     */
    public function createSpan($span) : Span
    {
        return $this->kernel->build('Span', $this, $span);
    }

    public function getIncludeAllowed() : bool
    {
        return $this->includeAllowed;
    }

    public function getIncludeRoot() : string
    {
        return $this->includeRoot;
    }

    public function setIncludePolicy(bool $includeAllowed, ?string $directory = null) : self
    {
        $this->includeAllowed = $includeAllowed;

        if ($directory !== null) {
            $this->includeRoot = $directory;
        }

        return $this;
    }
}
