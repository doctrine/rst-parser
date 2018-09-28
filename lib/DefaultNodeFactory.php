<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Nodes\AnchorNode;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Nodes\SeparatorNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Parser\LineChecker;
use InvalidArgumentException;
use function sprintf;

class DefaultNodeFactory implements NodeFactory
{
    /** @var NodeInstantiator[] */
    private $nodeInstantiators = [];

    public function __construct(NodeInstantiator ...$nodeInstantiators)
    {
        foreach ($nodeInstantiators as $nodeInstantiator) {
            $this->nodeInstantiators[$nodeInstantiator->getType()] = $nodeInstantiator;
        }
    }

    public function createDocument(Environment $environment) : Document
    {
        /** @var Document $document */
        $document = $this->create(NodeTypes::DOCUMENT, [$environment]);

        return $document;
    }

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function createToc(Environment $environment, array $files, array $options) : TocNode
    {
        /** @var TocNode $tocNode */
        $tocNode = $this->create(NodeTypes::TOC, [$environment, $files, $options]);

        return $tocNode;
    }

    public function createTitle(Node $value, int $level, string $token) : TitleNode
    {
        /** @var TitleNode $titleNode */
        $titleNode = $this->create(NodeTypes::TITLE, [$value, $level, $token]);

        return $titleNode;
    }

    public function createSeparator(int $level) : SeparatorNode
    {
        /** @var SeparatorNode $separatorNode */
        $separatorNode = $this->create(NodeTypes::SEPARATOR, [$level]);

        return $separatorNode;
    }

    /**
     * @param string[] $lines
     */
    public function createCode(array $lines) : CodeNode
    {
        /** @var CodeNode $codeNode */
        $codeNode = $this->create(NodeTypes::CODE, [$lines]);

        return $codeNode;
    }

    /**
     * @param string[] $lines
     */
    public function createQuote(array $lines) : QuoteNode
    {
        /** @var QuoteNode $quoteNode */
        $quoteNode = $this->create(NodeTypes::QUOTE, [$lines]);

        return $quoteNode;
    }

    /**
     * @param Node|string|null $value
     */
    public function createParagraph($value = null) : ParagraphNode
    {
        /** @var ParagraphNode $paragraphNode */
        $paragraphNode = $this->create(NodeTypes::PARAGRAPH, [$value]);

        return $paragraphNode;
    }

    /**
     * @param Node|string|null $value
     */
    public function createAnchor($value = null) : AnchorNode
    {
        /** @var AnchorNode $anchorNode */
        $anchorNode = $this->create(NodeTypes::ANCHOR, [$value]);

        return $anchorNode;
    }

    /**
     * @param Node|string|null $value
     */
    public function createList($value = null) : ListNode
    {
        /** @var ListNode $listNode */
        $listNode = $this->create(NodeTypes::LIST, [$value]);

        return $listNode;
    }

    /**
     * @param string[] $parts
     */
    public function createTable(array $parts, string $type, LineChecker $lineChecker) : TableNode
    {
        /** @var TableNode $tableNode */
        $tableNode = $this->create(NodeTypes::TABLE, [$parts, $type, $lineChecker]);

        return $tableNode;
    }

    /**
     * @param string|string[]|Span $span
     */
    public function createSpan(Parser $parser, $span) : Span
    {
        /** @var Span $span */
        $span = $this->create(NodeTypes::SPAN, [$parser, $span]);

        return $span;
    }

    /**
     * @param mixed[] $arguments
     */
    private function create(string $type, array $arguments) : Node
    {
        return $this->getNodeInstantiator($type)->create($arguments);
    }

    private function getNodeInstantiator(string $type) : NodeInstantiator
    {
        if (! isset($this->nodeInstantiators[$type])) {
            throw new InvalidArgumentException(sprintf('Could not find node instantiator of type %s', $type));
        }

        return $this->nodeInstantiators[$type];
    }
}
