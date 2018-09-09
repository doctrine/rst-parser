<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\RST\Directive;
use Doctrine\RST\Document;
use Doctrine\RST\Environment;
use Doctrine\RST\Factory;
use Doctrine\RST\FileIncluder;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Parser\Directive as ParserDirective;
use function explode;
use function sprintf;
use function str_replace;
use function strlen;
use function substr;
use function trim;

class DocumentParser
{
    /** @var Parser */
    private $parser;

    /** @var Environment */
    private $environment;

    /** @var Factory */
    private $factory;

    /** @var Directive[] */
    private $directives = [];

    /** @var bool */
    private $includeAllowed = true;

    /** @var string */
    private $includeRoot = '';

    /** @var Document */
    private $document;

    /** @var null|false|string */
    private $specialLetter;

    /** @var null|ParserDirective */
    private $directive;

    /** @var LineDataParser */
    private $lineDataParser;

    /** @var LineChecker */
    private $lineChecker;

    /** @var TableParser */
    private $tableParser;

    /** @var Buffer */
    private $buffer;

    /** @var Node|null */
    private $nodeBuffer;

    /** @var bool */
    private $isCode = false;

    /** @var int */
    private $state;

    /** @var null|ListLine */
    private $listLine;

    /** @var bool */
    private $listFlow = false;

    /**
     * @param Directive[] $directives
     */
    public function __construct(
        Parser $parser,
        Environment $environment,
        Factory $factory,
        array $directives,
        bool $includeAllowed,
        string $includeRoot
    ) {
        $this->parser         = $parser;
        $this->environment    = $environment;
        $this->factory        = $factory;
        $this->directives     = $directives;
        $this->includeAllowed = $includeAllowed;
        $this->includeRoot    = $includeRoot;
        $this->lineDataParser = new LineDataParser();
        $this->lineChecker    = new LineChecker($this->lineDataParser);
        $this->tableParser    = new TableParser();
        $this->buffer         = new Buffer();
    }

    public function getDocument() : Document
    {
        return $this->document;
    }

    public function parse(string $contents) : Document
    {
        $this->document = $this->factory->createDocument($this->environment);

        $this->init();

        $this->parseLines(trim($contents));

        foreach ($this->directives as $name => $directive) {
            $directive->finalize($this->document);
        }

        return $this->document;
    }

    private function init() : void
    {
        $this->specialLetter = false;
        $this->buffer        = new Buffer();
        $this->nodeBuffer    = null;
    }

    private function initDirective(string $line) : bool
    {
        $parserDirective = $this->lineDataParser->parseDirective($line);

        if ($parserDirective !== null) {
            $this->directive = $parserDirective;

            return true;
        }

        return false;
    }

    private function prepareCode() : bool
    {
        $lastLine = $this->buffer->getLastLine();

        if ($lastLine === null) {
            return false;
        }

        $lastline = trim($lastLine);

        if (strlen($lastLine) >= 2) {
            if (substr($lastLine, -2) === '::') {
                if (trim($lastLine) === '::') {
                    $this->buffer->pop();
                } else {
                    $this->buffer->set($this->buffer->count() - 1, substr($lastLine, 0, -1));
                }

                return true;
            }
        }

        return false;
    }

    private function isDirectiveOption(string $line) : bool
    {
        if ($this->directive === null) {
            return false;
        }

        $directiveOption = $this->lineDataParser->parseDirectiveOption($line);

        if ($directiveOption === null) {
            return false;
        }

        $this->directive->setOption($directiveOption->getName(), $directiveOption->getValue());

        return true;
    }

    private function getCurrentDirective() : ?Directive
    {
        if ($this->directive === null) {
            return null;
        }

        $name = $this->directive->getName();

        if (! isset($this->directives[$name])) {
            $message = 'Unknown directive: ' . $name;

            $this->environment->getErrorManager()->error($message);

            return null;
        }

        return $this->directives[$name];
    }

    private function hasBuffer() : bool
    {
        return ! $this->buffer->isEmpty() || $this->nodeBuffer !== null;
    }

    private function flush() : void
    {
        $node = null;

        $this->isCode = false;

        if ($this->hasBuffer()) {
            switch ($this->state) {
                case State::TITLE:
                    $data = $this->buffer->getLinesString();

                    $level = $this->environment->getLevel((string) $this->specialLetter);

                    $token = $this->environment->createTitle($level);

                    $node = $this->factory->createTitleNode(
                        $this->parser->createSpan($data),
                        $level,
                        $token
                    );

                    break;

                case State::SEPARATOR:
                    $level = $this->environment->getLevel((string) $this->specialLetter);

                    $node = $this->factory->createSeparatorNode($level);

                    break;

                case State::CODE:
                    /** @var string[] $buffer */
                    $buffer = $this->buffer->getLines();

                    $node = $this->factory->createCodeNode($buffer);

                    break;

                case State::BLOCK:
                    /** @var string[] $buffer */
                    $buffer = $this->buffer->getLines();

                    $node = $this->factory->createQuoteNode($buffer);

                    /** @var string $data */
                    $data = $node->getValue();

                    $document = $this->parser->getSubParser()->parseLocal($data);

                    $node->setValue($document);

                    break;

                case State::LIST:
                    $this->parseListLine(null, true);

                    /** @var ListNode $node */
                    $node = $this->nodeBuffer;

                    break;

                case State::TABLE:
                    /** @var TableNode $node */
                    $node = $this->nodeBuffer;

                    $node->finalize($this->parser);

                    break;

                case State::NORMAL:
                    $this->isCode = $this->prepareCode();

                    /** @var string $buffer */
                    $buffer = $this->buffer->getLines();

                    $node = $this->factory->createParagraphNode($this->parser->createSpan($buffer));

                    break;
            }
        }

        if ($this->directive !== null) {
            $currentDirective = $this->getCurrentDirective();

            if ($currentDirective !== null) {
                $currentDirective->process(
                    $this->parser,
                    $node,
                    $this->directive->getVariable(),
                    $this->directive->getData(),
                    $this->directive->getOptions()
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

    private function parseLine(string &$line) : bool
    {
        switch ($this->state) {
            case State::BEGIN:
                if (trim($line) !== '') {
                    if ($this->lineChecker->isListLine($line, $this->isCode)) {
                        $this->state = State::LIST;

                        /** @var ListNode $listNode */
                        $listNode = $this->factory->createListNode();

                        $this->nodeBuffer = $listNode;

                        $this->listLine = null;
                        $this->listFlow = true;

                        return false;
                    } elseif ($this->lineChecker->isBlockLine($line)) {
                        if ($this->isCode) {
                            $this->state = State::CODE;
                        } else {
                            $this->state = State::BLOCK;
                        }
                        return false;
                    } elseif ($this->lineChecker->isDirective($line)) {
                        $this->state  = State::DIRECTIVE;
                        $this->buffer = new Buffer();
                        $this->flush();
                        $this->initDirective($line);
                    } elseif ($this->parseLink($line)) {
                        return true;
                    } else {
                        $tableParts = $this->tableParser->parseTableLine($line);

                        if ($tableParts === null) {
                            $this->state = State::NORMAL;

                            return false;
                        }

                        $this->state = State::TABLE;

                        $tableNode = $this->factory->createTableNode($tableParts);

                        $this->nodeBuffer = $tableNode;
                    }
                }
                break;

            case State::LIST:
                if (! $this->parseListLine($line)) {
                    $this->flush();
                    $this->state = State::BEGIN;
                    return false;
                }
                break;

            case State::TABLE:
                if (trim($line) === '') {
                    $this->flush();
                    $this->state = State::BEGIN;
                } else {
                    $parts = $this->tableParser->parseTableLine($line);

                    if ($this->nodeBuffer instanceof TableNode && ! $this->nodeBuffer->push($parts, $line)) {
                        $this->flush();

                        $this->state = State::BEGIN;

                        return false;
                    }
                }

                break;

            case State::NORMAL:
                if (trim($line) !== '') {
                    $specialLetter = $this->lineChecker->isSpecialLine($line);

                    if ($specialLetter !== null) {
                        $this->specialLetter = $specialLetter;

                        $lastLine = $this->buffer->pop();

                        if ($lastLine !== null) {
                            $this->buffer = new Buffer([$lastLine]);
                            $this->state  = State::TITLE;
                        } else {
                            $this->buffer->push($line);
                            $this->state = State::SEPARATOR;
                        }
                        $this->flush();
                        $this->state = State::BEGIN;
                    } else {
                        if ($this->lineChecker->isDirective($line)) {
                            $this->flush();
                            $this->state = State::BEGIN;
                            return false;
                        }
                        if ($this->lineChecker->isComment($line)) {
                            $this->flush();
                            $this->state = State::COMMENT;
                        } else {
                            $this->buffer->push($line);
                        }
                    }
                } else {
                    $this->flush();
                    $this->state = State::BEGIN;
                }
                break;

            case State::COMMENT:
                $isComment = false;

                if (! $this->lineChecker->isComment($line) && (trim($line) === '' || $line[0] !== ' ')) {
                    $this->state = State::BEGIN;
                    return false;
                }
                break;

            case State::BLOCK:
            case State::CODE:
                if (! $this->lineChecker->isBlockLine($line)) {
                    $this->flush();
                    $this->state = State::BEGIN;
                    return false;
                } else {
                    $this->buffer->push($line);
                }
                break;

            case State::DIRECTIVE:
                if (! $this->isDirectiveOption($line)) {
                    if (! $this->lineChecker->isDirective($line)) {
                        $directive    = $this->getCurrentDirective();
                        $this->isCode = $directive !== null ? $directive->wantCode() : false;
                        $this->state  = State::BEGIN;

                        return false;
                    }

                    $this->flush();
                    $this->initDirective($line);
                }
                break;

            default:
                $this->environment->getErrorManager()->error('Parser ended in an unexcepted state');
        }

        return true;
    }

    private function parseLines(string $document) : void
    {
        // Including files
        $document = str_replace("\r\n", "\n", $document);
        $document = sprintf("\n%s\n", $document);

        $document = (new FileIncluder(
            $this->environment,
            $this->includeAllowed,
            $this->includeRoot
        ))->includeFiles($document);

        // Removing UTF-8 BOM
        $bom      = "\xef\xbb\xbf";
        $document = str_replace($bom, '', $document);

        $lines       = explode("\n", $document);
        $this->state = State::BEGIN;

        foreach ($lines as $n => $line) {
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

    private function parseLink(string $line) : bool
    {
        $link = $this->lineDataParser->parseLink($line);

        if ($link === null) {
            return false;
        }

        if ($link->getType() === Link::TYPE_ANCHOR) {
            $anchorNode = $this->factory
                ->createAnchorNode($link->getName());

            $this->document->addNode($anchorNode);
        }

        $this->environment->setLink($link->getName(), $link->getUrl());

        return true;
    }

    private function parseListLine(?string $line, bool $flush = false) : bool
    {
        if ($line !== null && trim($line) !== '') {
            $listLine = $this->lineDataParser->parseListLine($line);

            if ($listLine !== null) {
                if ($this->listLine instanceof ListLine) {
                    $this->listLine->setText($this->parser->createSpan($this->listLine->getText()));

                    /** @var ListNode $listNode */
                    $listNode = $this->nodeBuffer;

                    $listNode->addLine($this->listLine->toArray());
                }
                $this->listLine = $listLine;
            } else {
                if ($this->listLine instanceof ListLine && ($this->listFlow || $line[0] === ' ')) {
                    $this->listLine->addText($line);
                } else {
                    $flush = true;
                }
            }
            $this->listFlow = true;
        } else {
            $this->listFlow = false;
        }

        if ($flush) {
            if ($this->listLine instanceof ListLine) {
                $this->listLine->setText($this->parser->createSpan($this->listLine->getText()));

                /** @var ListNode $listNode */
                $listNode = $this->nodeBuffer;

                $listNode->addLine($this->listLine->toArray());

                $this->listLine = null;
            }

            return false;
        }

        return true;
    }
}
