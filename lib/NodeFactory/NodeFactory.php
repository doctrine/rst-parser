<?php

declare(strict_types=1);

namespace Doctrine\RST\NodeFactory;

use Doctrine\RST\Environment;
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

interface NodeFactory
{
    public function createDocumentNode(Environment $environment) : DocumentNode;

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function createTocNode(Environment $environment, array $files, array $options) : TocNode;

    public function createTitleNode(Node $value, int $level, string $token) : TitleNode;

    public function createSeparatorNode(int $level) : SeparatorNode;

    /**
     * @param string[] $lines
     */
    public function createBlockNode(array $lines) : BlockNode;

    /**
     * @param string[] $lines
     */
    public function createCodeNode(array $lines) : CodeNode;

    public function createQuoteNode(DocumentNode $documentNode) : QuoteNode;

    public function createParagraphNode(SpanNode $span) : ParagraphNode;

    public function createAnchorNode(?string $value = null) : AnchorNode;

    public function createListNode() : ListNode;

    /**
     * @param string[] $parts
     */
    public function createTableNode(array $parts, string $type, LineChecker $lineChecker) : TableNode;

    /**
     * @param string|string[]|SpanNode $span
     */
    public function createSpanNode(Parser $parser, $span) : SpanNode;

    public function createDefinitionListNode(DefinitionList $definitionList) : DefinitionListNode;

    public function createWrapperNode(?Node $node, string $before = '', string $after = '') : WrapperNode;

    public function createFigureNode(ImageNode $image, ?Node $document = null) : FigureNode;

    /**
     * @param string[] $options
     */
    public function createImageNode(string $url, array $options = []) : ImageNode;

    public function createMetaNode(string $key, string $value) : MetaNode;

    public function createRawNode(string $value) : RawNode;

    /**
     * @param mixed[] $data
     */
    public function createDummyNode(array $data) : DummyNode;

    public function createMainNode() : MainNode;

    public function createCallableNode(callable $callable) : CallableNode;

    public function createSectionBeginNode(TitleNode $titleNode) : SectionBeginNode;

    public function createSectionEndNode(TitleNode $titleNode) : SectionEndNode;
}
