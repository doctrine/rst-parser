<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\Common\EventManager;
use Doctrine\RST\Configuration;
use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Environment;
use Doctrine\RST\Event\PostParseDocumentEvent;
use Doctrine\RST\Event\PreParseDocumentEvent;
use Doctrine\RST\FileIncluder;
use Doctrine\RST\Meta\LinkTarget;
use Doctrine\RST\NodeFactory\NodeFactory;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Parser\Directive as ParserDirective;
use Exception;
use Throwable;

use function array_reverse;
use function array_search;
use function assert;
use function chr;
use function explode;
use function fwrite;
use function getenv;
use function ltrim;
use function max;
use function sprintf;
use function str_replace;
use function strlen;
use function substr;
use function trim;

use const PHP_SAPI;
use const STDERR;

final class DocumentParser
{
    private Configuration $configuration;

    private Parser $parser;

    private Environment $environment;

    private NodeFactory $nodeFactory;

    private EventManager $eventManager;

    /** @var Directive[] */
    private array $directives = [];

    private bool $includeAllowed = true;

    private string $includeRoot = '';

    private ?DocumentNode $document = null;

    private string $specialLetter = '';

    private ?ParserDirective $directive = null;

    private LineDataParser $lineDataParser;

    private LineChecker $lineChecker;

    private TableParser $tableParser;

    private Buffer $buffer;

    private Buffer $anchorBuffer;

    private ?Node $nodeBuffer = null;

    private bool $isCode = false;

    private ?Lines $lines = null;

    private ?int $currentLineNumber = null;

    private ?string $state = null;

    private ?TitleNode $lastTitleNode = null;

    /** @var TitleNode[] */
    private array $openTitleNodes = [];

    private int $listOffset = 0;

    /** @var string|null */
    private $listMarker = null;

    private ?FieldOption $fieldOption = null;

    /** @param Directive[] $directives */
    public function __construct(
        Configuration $configuration,
        Parser $parser,
        Environment $environment,
        NodeFactory $nodeFactory,
        EventManager $eventManager,
        array $directives,
        bool $includeAllowed,
        string $includeRoot
    ) {
        $this->configuration  = $configuration;
        $this->parser         = $parser;
        $this->environment    = $environment;
        $this->nodeFactory    = $nodeFactory;
        $this->eventManager   = $eventManager;
        $this->directives     = $directives;
        $this->includeAllowed = $includeAllowed;
        $this->includeRoot    = $includeRoot;
        $this->lineDataParser = new LineDataParser($this->parser, $eventManager);
        $this->lineChecker    = new LineChecker();
        $this->tableParser    = new TableParser();
        $this->buffer         = new Buffer();
        $this->anchorBuffer   = new Buffer();
    }

    public function getDocument(): DocumentNode
    {
        return $this->document;
    }

    public function parse(string $contents): DocumentNode
    {
        $preParseDocumentEvent = new PreParseDocumentEvent($this->parser, $contents);

        $this->eventManager->dispatchEvent(
            PreParseDocumentEvent::PRE_PARSE_DOCUMENT,
            $preParseDocumentEvent
        );

        $this->document = $this->nodeFactory->createDocumentNode($this->environment);

        $this->init();

        $this->parseLines(trim($preParseDocumentEvent->getContents()));

        foreach ($this->directives as $name => $directive) {
            $directive->finalize($this->document);
        }

        $this->eventManager->dispatchEvent(
            PostParseDocumentEvent::POST_PARSE_DOCUMENT,
            new PostParseDocumentEvent($this->document)
        );

        return $this->document;
    }

    private function init(): void
    {
        $this->specialLetter = '';
        $this->buffer        = new Buffer();
        $this->anchorBuffer  = new Buffer();
        $this->nodeBuffer    = null;
        $this->listOffset    = 0;
        $this->listMarker    = null;
    }

    private function setState(string $state): void
    {
        $this->state = $state;
    }

    private function prepareDocument(string $document): string
    {
        $document = str_replace("\r\n", "\n", $document);
        $document = sprintf("\n%s\n", $document);

        $document = (new FileIncluder(
            $this->environment,
            $this->includeAllowed,
            $this->includeRoot
        ))->includeFiles($document);

        // Removing UTF-8 BOM
        $document = str_replace("\xef\xbb\xbf", '', $document);

        // Replace \u00a0 with " "
        $document = str_replace(chr(194) . chr(160), ' ', $document);

        return $document;
    }

    private function createLines(string $document): Lines
    {
        return new Lines(explode("\n", $document));
    }

    private function parseLines(string $document): void
    {
        $document = $this->prepareDocument($document);

        $this->lines = $this->createLines($document);
        $this->setState(State::BEGIN);

        foreach ($this->lines as $i => $line) {
            $this->currentLineNumber = $i + 1;
            while (true) {
                if ($this->parseLine($line)) {
                    break;
                }
            }
        }

        $this->currentLineNumber = null;

        // DocumentNode is flushed twice to trigger the directives
        $this->flush();
        $this->flush();

        foreach ($this->openTitleNodes as $titleNode) {
            $this->endOpenSection($titleNode);
        }
    }

    /**
     * Return true if this line has completed process.
     *
     * If false is returned, this function will be called again with the same line.
     * This is useful when you switched state and want to parse the line again
     * with the new state (e.g. when the end of a list is found, you want the line
     * to be parsed as "BEGIN" again).
     */
    private function parseLine(string $line): bool
    {
        if (getenv('SHELL_VERBOSITY') >= 3 && PHP_SAPI === 'cli') {
            fwrite(STDERR, sprintf("Parsing line: %s\n", $line));
        }

        switch ($this->state) {
            case State::BEGIN:
                if (trim($line) !== '') {
                    if ($this->lineChecker->isListLine($line, $this->listMarker, $this->listOffset, $this->lines->getNextLine())) {
                        $this->setState(State::LIST);
                        $this->buffer->push($line);

                        return true;
                    }

                    // Represents a literal block here the entire line is literally "::"
                    // Ref: https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html#literal-blocks
                    //  > If it occurs as a paragraph of its own, that paragraph is completely left out of the document.
                    if (trim($line) === '::') {
                        $this->isCode = true;

                        // return true to move onto the next line, this line is omitted
                        return true;
                    }

                    if ($this->lineChecker->isBlockLine($line)) {
                        if ($this->isCode) {
                            $this->setState(State::CODE);
                        } else {
                            $this->setState(State::BLOCK);
                        }

                        return false;
                    }

                    if ($this->lineChecker->isComment($line)) {
                        $this->flush();
                        $this->setState(State::COMMENT);

                        return false;
                    }

                    if ($this->lineDataParser->parseLinkTarget($line) !== null) {
                        $this->anchorBuffer->push($line);

                        return true;
                    }

                    if ($this->lineChecker->isDirective($line)) {
                        $this->setState(State::DIRECTIVE);
                        $this->buffer = new Buffer();
                        $this->flush();
                        $this->initDirective($line);

                        return true;
                    }

                    $separatorLineConfig = $this->tableParser->parseTableSeparatorLine($line);
                    if ($separatorLineConfig !== null) {
                        $this->setState(State::TABLE);

                        $tableNode = $this->nodeFactory->createTableNode(
                            $separatorLineConfig,
                            $this->tableParser->guessTableType($line),
                            $this->lineChecker
                        );

                        $this->nodeBuffer = $tableNode;

                        return true;
                    }

                    if ($this->lineChecker->isFieldOption($line)) {
                        $this->setState(State::FIELD_LIST);
                        $this->buffer->push($line);

                        return true;
                    }

                    if ($this->lineChecker->isIndented($this->lines->getNextLine())) {
                        $this->setState(State::DEFINITION_LIST);
                        $this->buffer->push($line);

                        return true;
                    }

                    if ($this->getCurrentDirective() !== null && ! $this->getCurrentDirective()->appliesToNonBlockContent()) {
                        // If there is a directive set, it means we are the line *after* that directive
                        // But the state is being set to NORMAL, which means we are a non-indented line.
                        // Some special directives (like class) allow their content to be non-indented.
                        // But most do not, which means that our directive is now finished.
                        // We flush so that the directive can be processed. It will be passed a
                        // null node (We know because we are currently in a NEW state. If there
                        // had been legitimately-indented content, that would have matched some
                        // other state (e.g. BLOCK or CODE) and flushed when it finished.
                        $this->flush();
                    }

                    $this->setState(State::NORMAL);

                    return false;
                }

                break;

            case State::LIST:
                if (! $this->lineChecker->isListLine($line, $this->listMarker, $this->listOffset) && ! $this->lineChecker->isBlockLine($line, max(1, $this->listOffset))) {
                    if (trim($this->lines->getPreviousLine()) !== '') {
                        $this->configuration->getErrorManager()->warning(
                            'List ends without a blank line; unexpected unindent',
                            $this->environment->getCurrentFileName(),
                            $this->currentLineNumber !== null ? $this->currentLineNumber - 1 : null
                        );
                    }

                    return $this->flushAndResetParsing();
                }

                // the list item offset is determined by the offset of the first text.
                // An offset of 1 or lower indicates that the list line didn't contain any text.
                if ($this->listOffset <= 1) {
                    $this->listOffset = strlen($line) - strlen(ltrim($line));
                }

                $this->buffer->push($line);

                break;

            case State::DEFINITION_LIST:
                if ($this->lineChecker->isDefinitionListEnded($line, $this->lines->getNextLine())) {
                    return $this->flushAndResetParsing();
                }

                $this->buffer->push($line);

                break;

            case State::FIELD_LIST:
                if ($this->lineChecker->isFieldListEnded($line)) {
                    return $this->flushAndResetParsing();
                }

                $this->buffer->push($line);

                break;

            case State::TABLE:
                if (trim($line) === '') {
                    $this->flush();
                    $this->setState(State::BEGIN);
                } else {
                    $separatorLineConfig = $this->tableParser->parseTableSeparatorLine($line);

                    // not sure if this is possible, being cautious
                    if (! $this->nodeBuffer instanceof TableNode) {
                        throw new Exception('Node Buffer should be a TableNode instance');
                    }

                    // push the separator or content line onto the TableNode
                    if ($separatorLineConfig !== null) {
                        $this->nodeBuffer->pushSeparatorLine($separatorLineConfig);
                    } else {
                        $this->nodeBuffer->pushContentLine($line);
                    }
                }

                break;

            case State::NORMAL:
                if (trim($line) !== '') {
                    $specialLetter = $this->lineChecker->isSpecialLine($line);

                    if ($specialLetter !== null) {
                        $this->specialLetter .= $specialLetter;

                        $lastLine = $this->buffer->pop();

                        if ($lastLine === null) {
                            $this->setState(State::SPECIAL);

                            return true;
                        }

                        $this->buffer = new Buffer([$lastLine]);
                        $this->setState(State::TITLE);

                        $this->flush();
                        $this->setState(State::BEGIN);
                    } elseif ($this->lineChecker->isDirective($line)) {
                        return $this->flushAndResetParsing();
                    } elseif ($this->lineChecker->isComment($line)) {
                        $this->flush();
                        $this->setState(State::COMMENT);
                    } else {
                        $this->buffer->push($line);
                    }
                } else {
                    $this->flush();
                    $this->setState(State::BEGIN);
                }

                break;

            case State::SPECIAL:
                // One special line was found initially. This might be a separator or title
                if (trim($line) === '') {
                    $this->buffer->push($line);
                    $this->setState(State::SEPARATOR);

                    $this->flush();
                    $this->setState(State::BEGIN);
                } else {
                    $specialLetter = $this->lineChecker->isSpecialLine($line);
                    if ($specialLetter !== null) {
                        $this->specialLetter .= $specialLetter;
                        $this->setState(State::TITLE);
                        $this->flush();
                        $this->setState(State::BEGIN);
                    } else {
                        $this->buffer->push($line);
                    }
                }

                break;

            case State::COMMENT:
                if (! $this->lineChecker->isComment($line) && (trim($line) === '' || $line[0] !== ' ')) {
                    $this->setState(State::BEGIN);

                    return false;
                }

                break;

            case State::BLOCK:
            case State::CODE:
                if (! $this->lineChecker->isBlockLine($line)) {
                    // the previous line(s) was in a block (indented), but
                    // this line is no longer indented
                    return $this->flushAndResetParsing();
                } else {
                    $this->buffer->push($line);
                }

                break;

            case State::DIRECTIVE:
                if ($this->lineChecker->isDirective($line) && $this->directive === null) {
                    $this->flush();
                    $this->initDirective($line);

                    break;
                }

                if ($this->fieldOption !== null && $this->lineChecker->isBlockLine($line, $this->fieldOption->getOffset())) {
                    $this->fieldOption->appendLine($line);

                    break;
                }

                if ($this->lineChecker->isFieldOption($line)) {
                    if ($this->directive !== null && $this->fieldOption !== null) {
                        $this->directive->setOption($this->fieldOption->getName(), $this->fieldOption->getBody());
                    }

                    $this->fieldOption = $this->lineDataParser->parseFieldOption($line);

                    break;
                }

                $directive    = $this->getCurrentDirective();
                $this->isCode = $directive !== null ? $directive->wantCode() : false;
                $this->setState(State::BEGIN);

                return false;

            default:
                $this->configuration->getErrorManager()->error('Parser ended in an unexcepted state');
        }

        return true;
    }

    private function flush(): void
    {
        $node = null;

        $this->isCode = false;

        if (! $this->anchorBuffer->isEmpty() && $this->state !== State::TITLE) {
            foreach ($this->anchorBuffer->getLines() as $anchorLine) {
                $this->createAnchorNode($anchorLine);
            }
        }

        if ($this->hasBuffer()) {
            switch ($this->state) {
                case State::TITLE:
                    $data = $this->buffer->getLinesString();

                    $level = $this->environment->getLevel($this->specialLetter);
                    $level = $this->environment->getConfiguration()->getInitialHeaderLevel() + $level - 1;

                    $token = $this->environment->createTitle($level);

                    $titleAnchor = Environment::slugify($data);
                    foreach ($this->anchorBuffer->getLines() as $anchorLine) {
                        $link = $this->lineDataParser->parseLinkTarget($anchorLine);
                        if ($link === null) {
                            continue;
                        }

                        $url        = Environment::slugify($link->getUrl());
                        $linkTarget = new LinkTarget($link->getName(), $url, $data);
                        $this->environment->setLinkTarget($linkTarget);
                        $titleAnchor = Environment::slugify($linkTarget->getName());
                    }

                    $node = $this->nodeFactory->createTitleNode(
                        $this->parser->createSpanNode($data),
                        $level,
                        $token,
                        $titleAnchor
                    );

                    if ($this->lastTitleNode !== null) {
                        // current level is less than previous so we need to
                        // end previous open sections with a greater or equal level
                        if ($node->getLevel() < $this->lastTitleNode->getLevel()) {
                            foreach (array_reverse($this->openTitleNodes) as $titleNode) {
                                if ($node->getLevel() > $titleNode->getLevel()) {
                                    break;
                                }

                                $this->endOpenSection($titleNode);
                            }
                        // same level as the last so just close the last open section
                        } elseif ($node->getLevel() === $this->lastTitleNode->getLevel()) {
                            $this->endOpenSection($this->lastTitleNode);
                        }
                    }

                    $this->lastTitleNode = $node;

                    $this->document->addNode(
                        $this->nodeFactory->createSectionBeginNode($node)
                    );

                    $this->openTitleNodes[] = $node;

                    break;

                case State::SEPARATOR:
                    $level = $this->environment->getLevel($this->specialLetter);

                    $node = $this->nodeFactory->createSeparatorNode($level);

                    break;

                case State::CODE:
                    /** @var string[] $buffer */
                    $buffer = $this->buffer->getLines();

                    $node = $this->nodeFactory->createCodeNode($buffer);

                    break;

                case State::BLOCK:
                    /** @var string[] $lines */
                    $lines = $this->buffer->getLines();

                    $node = $this->nodeFactory->createBlockNode($lines);

                    // This means we are in an indented area that is not a code block
                    // or definition list.
                    // If we're NOT in a directive, then this must be a blockquote.
                    // If we ARE in a directive, allow the directive to convert
                    // the BlockNode into what it needs
                    if ($this->directive === null) {
                        $document = $this->parser->getSubParser()->parseLocal($node->getValue());

                        $node = $this->nodeFactory->createQuoteNode($document);
                    }

                    break;

                case State::LIST:
                    $list = $this->lineDataParser->parseList(
                        $this->buffer->getLines()
                    );

                    $node = $this->nodeFactory->createListNode($list, $list[0]->isOrdered());

                    break;

                case State::DEFINITION_LIST:
                    $definitionList = $this->lineDataParser->parseDefinitionList(
                        $this->buffer->getLines()
                    );

                    $node = $this->nodeFactory->createDefinitionListNode($definitionList);

                    break;

                case State::FIELD_LIST:
                    $fieldList = $this->lineDataParser->parseFieldList(
                        $this->buffer->getLines()
                    );

                    $node = $this->nodeFactory->createFieldListNode($fieldList);

                    break;

                case State::TABLE:
                    $node = $this->nodeBuffer;
                    assert($node instanceof TableNode);

                    $node->finalize($this->parser);

                    break;

                case State::NORMAL:
                    $this->isCode = $this->prepareCode();

                    $buffer = $this->buffer->getLinesString();

                    $node = $this->nodeFactory->createParagraphNode($this->parser->createSpanNode($buffer));

                    break;
            }
        }

        if ($this->directive !== null) {
            $currentDirective = $this->getCurrentDirective();

            if ($this->fieldOption !== null) {
                $this->directive->setOption($this->fieldOption->getName(), $this->fieldOption->getBody());
                $this->fieldOption = null;
            }

            if ($currentDirective !== null) {
                try {
                    $currentDirective->process(
                        $this->parser,
                        $node,
                        $this->directive->getVariable(),
                        $this->directive->getData(),
                        $this->directive->getOptions()
                    );
                } catch (Throwable $e) {
                    $this->configuration->getErrorManager()->error(
                        sprintf('Error while processing "%s" directive: "%s"', $currentDirective->getName(), $e->getMessage()),
                        $this->environment->getCurrentFileName(),
                        $this->currentLineNumber ?? null,
                        $e
                    );
                }
            }

            $node = null;
        }

        $this->directive = null;

        if ($node !== null) {
            $this->document->addNode($node);
        }

        $this->init();
    }

    private function hasBuffer(): bool
    {
        return ! $this->buffer->isEmpty() || $this->nodeBuffer !== null;
    }

    private function getCurrentDirective(): ?Directive
    {
        if ($this->directive === null) {
            return null;
        }

        $name = $this->directive->getName();

        return $this->directives[$name];
    }

    private function initDirective(string $line): bool
    {
        $parserDirective = $this->lineDataParser->parseDirective($line);

        if ($parserDirective === null) {
            return false;
        }

        if (! isset($this->directives[$parserDirective->getName()])) {
            $this->configuration->getErrorManager()->error(
                sprintf('Unknown directive "%s" for line "%s"', $parserDirective->getName(), $line),
                $this->environment->getCurrentFileName()
            );

            return false;
        }

        $this->directive = $parserDirective;

        return true;
    }

    /**
     * Called on a NORMAL state line: it's used to determine if this
     * it beginning a code block - by having a line ending in "::"
     */
    private function prepareCode(): bool
    {
        $lastLine = $this->buffer->getLastLine();

        if ($lastLine === null) {
            return false;
        }

        $trimmedLastLine = trim($lastLine);

        if (strlen($trimmedLastLine) >= 2) {
            if (substr($trimmedLastLine, -2) === '::') {
                if (trim($trimmedLastLine) === '::') {
                    $this->buffer->pop();
                } else {
                    $this->buffer->set($this->buffer->count() - 1, substr($trimmedLastLine, 0, -1));
                }

                return true;
            }
        }

        return false;
    }

    private function createAnchorNode(string $line): bool
    {
        $link = $this->lineDataParser->parseLinkTarget($line);

        if ($link === null) {
            return false;
        }

        if ($link->getType() === Link::TYPE_ANCHOR) {
            $anchorNode = $this->nodeFactory
                ->createAnchorNode($link->getName());

            $this->document->addNode($anchorNode);
        }

        $linkTarget = new LinkTarget($link->getName(), $link->getUrl());
        $this->environment->setLinkTarget($linkTarget);

        return true;
    }

    private function endOpenSection(TitleNode $titleNode): void
    {
        $this->document->addNode(
            $this->nodeFactory->createSectionEndNode($titleNode)
        );

        $key = array_search($titleNode, $this->openTitleNodes, true);

        if ($key === false) {
            return;
        }

        unset($this->openTitleNodes[$key]);
    }

    /** @return false */
    private function flushAndResetParsing(): bool
    {
        $this->flush();
        $this->setState(State::BEGIN);

        return false;
    }
}
