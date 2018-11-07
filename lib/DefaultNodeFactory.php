<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Nodes\AnchorNode;
use Doctrine\RST\Nodes\BlockNode;
use Doctrine\RST\Nodes\CallableNode;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\DefinitionListNode;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\DummyNode;
use Doctrine\RST\Nodes\FigureNode;
use Doctrine\RST\Nodes\ImageNode;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\MainNode;
use Doctrine\RST\Nodes\MetaNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Nodes\SeparatorNode;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser\DefinitionList;
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

    public function createDocumentNode(Environment $environment) : DocumentNode
    {
        /** @var DocumentNode $document */
        $document = $this->create(NodeTypes::DOCUMENT, [$environment]);

        return $document;
    }

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function createTocNode(Environment $environment, array $files, array $options) : TocNode
    {
        /** @var TocNode $tocNode */
        $tocNode = $this->create(NodeTypes::TOC, [$environment, $files, $options]);

        return $tocNode;
    }

    public function createTitleNode(Node $value, int $level, string $token) : TitleNode
    {
        /** @var TitleNode $titleNode */
        $titleNode = $this->create(NodeTypes::TITLE, [$value, $level, $token]);

        return $titleNode;
    }

    public function createSeparatorNode(int $level) : SeparatorNode
    {
        /** @var SeparatorNode $separatorNode */
        $separatorNode = $this->create(NodeTypes::SEPARATOR, [$level]);

        return $separatorNode;
    }

    /**
     * @param string[] $lines
     */
    public function createBlockNode(array $lines) : BlockNode
    {
        /** @var BlockNode $blockNode */
        $blockNode = $this->create(NodeTypes::BLOCK, [$lines]);

        return $blockNode;
    }

    /**
     * @param string[] $lines
     */
    public function createCodeNode(array $lines) : CodeNode
    {
        /** @var CodeNode $codeNode */
        $codeNode = $this->create(NodeTypes::CODE, [$lines]);

        return $codeNode;
    }

    public function createQuoteNode(DocumentNode $documentNode) : QuoteNode
    {
        /** @var QuoteNode $quoteNode */
        $quoteNode = $this->create(NodeTypes::QUOTE, [$documentNode]);

        return $quoteNode;
    }

    public function createParagraphNode(SpanNode $span) : ParagraphNode
    {
        /** @var ParagraphNode $paragraphNode */
        $paragraphNode = $this->create(NodeTypes::PARAGRAPH, [$span]);

        return $paragraphNode;
    }

    public function createAnchorNode(?string $value = null) : AnchorNode
    {
        /** @var AnchorNode $anchorNode */
        $anchorNode = $this->create(NodeTypes::ANCHOR, [$value]);

        return $anchorNode;
    }

    public function createListNode() : ListNode
    {
        /** @var ListNode $listNode */
        $listNode = $this->create(NodeTypes::LIST, []);

        return $listNode;
    }

    /**
     * @param string[] $parts
     */
    public function createTableNode(array $parts, string $type, LineChecker $lineChecker) : TableNode
    {
        /** @var TableNode $tableNode */
        $tableNode = $this->create(NodeTypes::TABLE, [$parts, $type, $lineChecker]);

        return $tableNode;
    }

    /**
     * @param string|string[]|SpanNode $span
     */
    public function createSpanNode(Parser $parser, $span) : SpanNode
    {
        /** @var SpanNode $span */
        $span = $this->create(NodeTypes::SPAN, [$parser, $span]);

        return $span;
    }

    public function createDefinitionListNode(DefinitionList $definitionList) : DefinitionListNode
    {
        /** @var DefinitionListNode $definitionListNode */
        $definitionListNode = $this->create(NodeTypes::DEFINITION_LIST, [$definitionList]);

        return $definitionListNode;
    }

    public function createWrapperNode(?Node $node, string $before = '', string $after = '') : WrapperNode
    {
        /** @var WrapperNode $wrapperNode */
        $wrapperNode = $this->create(NodeTypes::WRAPPER, [$node, $before, $after]);

        return $wrapperNode;
    }

    public function createFigureNode(ImageNode $image, ?Node $document = null) : FigureNode
    {
        /** @var FigureNode $figureNode */
        $figureNode = $this->create(NodeTypes::FIGURE, [$image, $document]);

        return $figureNode;
    }

    /**
     * @param string[] $options
     */
    public function createImageNode(string $url, array $options = []) : ImageNode
    {
        /** @var ImageNode $imageNode */
        $imageNode = $this->create(NodeTypes::IMAGE, [$url, $options]);

        return $imageNode;
    }

    public function createMetaNode(string $key, string $value) : MetaNode
    {
        /** @var MetaNode $metaNode */
        $metaNode = $this->create(NodeTypes::META, [$key, $value]);

        return $metaNode;
    }

    public function createRawNode(string $value) : RawNode
    {
        /** @var RawNode $rawNode */
        $rawNode = $this->create(NodeTypes::RAW, [$value]);

        return $rawNode;
    }

    /**
     * @param mixed[] $data
     */
    public function createDummyNode(array $data) : DummyNode
    {
        /** @var DummyNode $dummyNode */
        $dummyNode = $this->create(NodeTypes::DUMMY, [$data]);

        return $dummyNode;
    }

    public function createMainNode() : MainNode
    {
        /** @var MainNode $mainNode */
        $mainNode = $this->create(NodeTypes::MAIN, []);

        return $mainNode;
    }

    public function createCallableNode(callable $callable) : CallableNode
    {
        /** @var CallableNode $callableNode */
        $callableNode = $this->create(NodeTypes::CALLABLE, [$callable]);

        return $callableNode;
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
