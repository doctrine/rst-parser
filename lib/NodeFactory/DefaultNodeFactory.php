<?php

declare(strict_types=1);

namespace Doctrine\RST\NodeFactory;

use Doctrine\Common\EventManager;
use Doctrine\RST\Environment;
use Doctrine\RST\Event\PostNodeCreateEvent;
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
use Doctrine\RST\Nodes\NodeTypes;
use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Nodes\RawNode;
use Doctrine\RST\Nodes\SectionBeginNode;
use Doctrine\RST\Nodes\SectionEndNode;
use Doctrine\RST\Nodes\SeparatorNode;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Nodes\WrapperNode;
use Doctrine\RST\Parser;
use Doctrine\RST\Parser\DefinitionList;
use Doctrine\RST\Parser\LineChecker;
use Doctrine\RST\Parser\ListItem;
use InvalidArgumentException;

use function assert;
use function sprintf;

final class DefaultNodeFactory implements NodeFactory
{
    /** @var EventManager */
    private $eventManager;

    /** @var NodeInstantiator[] */
    private $nodeInstantiators = [];

    public function __construct(EventManager $eventManager, NodeInstantiator ...$nodeInstantiators)
    {
        $this->eventManager = $eventManager;

        foreach ($nodeInstantiators as $nodeInstantiator) {
            $this->nodeInstantiators[$nodeInstantiator->getType()] = $nodeInstantiator;
        }
    }

    public function createDocumentNode(Environment $environment): DocumentNode
    {
        $document = $this->create(NodeTypes::DOCUMENT, [$environment]);
        assert($document instanceof DocumentNode);

        return $document;
    }

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function createTocNode(Environment $environment, array $files, array $options): TocNode
    {
        $tocNode = $this->create(NodeTypes::TOC, [$environment, $files, $options]);
        assert($tocNode instanceof TocNode);

        return $tocNode;
    }

    public function createTitleNode(Node $value, int $level, string $token): TitleNode
    {
        $titleNode = $this->create(NodeTypes::TITLE, [$value, $level, $token]);
        assert($titleNode instanceof TitleNode);

        return $titleNode;
    }

    public function createSeparatorNode(int $level): SeparatorNode
    {
        $separatorNode = $this->create(NodeTypes::SEPARATOR, [$level]);
        assert($separatorNode instanceof SeparatorNode);

        return $separatorNode;
    }

    /**
     * @param string[] $lines
     */
    public function createBlockNode(array $lines): BlockNode
    {
        $blockNode = $this->create(NodeTypes::BLOCK, [$lines]);
        assert($blockNode instanceof BlockNode);

        return $blockNode;
    }

    /**
     * @param string[] $lines
     */
    public function createCodeNode(array $lines): CodeNode
    {
        $codeNode = $this->create(NodeTypes::CODE, [$lines]);
        assert($codeNode instanceof CodeNode);

        return $codeNode;
    }

    public function createQuoteNode(DocumentNode $documentNode): QuoteNode
    {
        $quoteNode = $this->create(NodeTypes::QUOTE, [$documentNode]);
        assert($quoteNode instanceof QuoteNode);

        return $quoteNode;
    }

    public function createParagraphNode(SpanNode $span): ParagraphNode
    {
        $paragraphNode = $this->create(NodeTypes::PARAGRAPH, [$span]);
        assert($paragraphNode instanceof ParagraphNode);

        return $paragraphNode;
    }

    public function createAnchorNode(?string $value = null): AnchorNode
    {
        $anchorNode = $this->create(NodeTypes::ANCHOR, [$value]);
        assert($anchorNode instanceof AnchorNode);

        return $anchorNode;
    }

    /**
     * @param ListItem[] $items
     */
    public function createListNode(array $items, bool $ordered): ListNode
    {
        $listNode = $this->create(NodeTypes::LIST, [$items, $ordered]);
        assert($listNode instanceof ListNode);

        return $listNode;
    }

    public function createTableNode(Parser\TableSeparatorLineConfig $separatorLineConfig, string $type, LineChecker $lineChecker): TableNode
    {
        $tableNode = $this->create(NodeTypes::TABLE, [$separatorLineConfig, $type, $lineChecker]);
        assert($tableNode instanceof TableNode);

        return $tableNode;
    }

    /**
     * @param string|string[]|SpanNode $span
     */
    public function createSpanNode(Parser $parser, $span): SpanNode
    {
        $span = $this->create(NodeTypes::SPAN, [$parser, $span]);
        assert($span instanceof SpanNode);

        return $span;
    }

    public function createDefinitionListNode(DefinitionList $definitionList): DefinitionListNode
    {
        $definitionListNode = $this->create(NodeTypes::DEFINITION_LIST, [$definitionList]);
        assert($definitionListNode instanceof DefinitionListNode);

        return $definitionListNode;
    }

    public function createWrapperNode(?Node $node, string $before = '', string $after = ''): WrapperNode
    {
        $wrapperNode = $this->create(NodeTypes::WRAPPER, [$node, $before, $after]);
        assert($wrapperNode instanceof WrapperNode);

        return $wrapperNode;
    }

    public function createFigureNode(ImageNode $image, ?Node $document = null): FigureNode
    {
        $figureNode = $this->create(NodeTypes::FIGURE, [$image, $document]);
        assert($figureNode instanceof FigureNode);

        return $figureNode;
    }

    /**
     * @param string[] $options
     */
    public function createImageNode(string $url, array $options = []): ImageNode
    {
        $imageNode = $this->create(NodeTypes::IMAGE, [$url, $options]);
        assert($imageNode instanceof ImageNode);

        return $imageNode;
    }

    public function createMetaNode(string $key, string $value): MetaNode
    {
        $metaNode = $this->create(NodeTypes::META, [$key, $value]);
        assert($metaNode instanceof MetaNode);

        return $metaNode;
    }

    public function createRawNode(string $value): RawNode
    {
        $rawNode = $this->create(NodeTypes::RAW, [$value]);
        assert($rawNode instanceof RawNode);

        return $rawNode;
    }

    /**
     * @param mixed[] $data
     */
    public function createDummyNode(array $data): DummyNode
    {
        $dummyNode = $this->create(NodeTypes::DUMMY, [$data]);
        assert($dummyNode instanceof DummyNode);

        return $dummyNode;
    }

    public function createMainNode(): MainNode
    {
        $mainNode = $this->create(NodeTypes::MAIN, []);
        assert($mainNode instanceof MainNode);

        return $mainNode;
    }

    public function createCallableNode(callable $callable): CallableNode
    {
        $callableNode = $this->create(NodeTypes::CALLABLE, [$callable]);
        assert($callableNode instanceof CallableNode);

        return $callableNode;
    }

    public function createSectionBeginNode(TitleNode $titleNode): SectionBeginNode
    {
        $sectionBeginNode = $this->create(NodeTypes::SECTION_BEGIN, [$titleNode]);
        assert($sectionBeginNode instanceof SectionBeginNode);

        return $sectionBeginNode;
    }

    public function createSectionEndNode(TitleNode $titleNode): SectionEndNode
    {
        $sectionEndNode = $this->create(NodeTypes::SECTION_END, [$titleNode]);
        assert($sectionEndNode instanceof SectionEndNode);

        return $sectionEndNode;
    }

    /**
     * @param mixed[] $arguments
     */
    private function create(string $type, array $arguments): Node
    {
        $node = $this->getNodeInstantiator($type)->create($arguments);

        $this->eventManager->dispatchEvent(
            PostNodeCreateEvent::POST_NODE_CREATE,
            new PostNodeCreateEvent($node)
        );

        return $node;
    }

    private function getNodeInstantiator(string $type): NodeInstantiator
    {
        if (! isset($this->nodeInstantiators[$type])) {
            throw new InvalidArgumentException(sprintf('Could not find node instantiator of type %s', $type));
        }

        return $this->nodeInstantiators[$type];
    }
}
