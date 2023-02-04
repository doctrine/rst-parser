<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML;

use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\HTML;
use Doctrine\RST\HTML\Directives\FormatDirectiveFactory;
use Doctrine\RST\Nodes;
use Doctrine\RST\Renderers;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;

final class HTMLFormat implements Format
{
    private TemplateRenderer $templateRenderer;

    private DirectiveFactory $directiveFactory;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
        $this->directiveFactory = new FormatDirectiveFactory();
    }

    public function getFileExtension(): string
    {
        return Format::HTML;
    }

    public function getDirectiveFactory(): DirectiveFactory
    {
        return $this->directiveFactory;
    }

    public function setDirectiveFactory(DirectiveFactory $directiveFactory): void
    {
        $this->directiveFactory = $directiveFactory;
    }

    /** @return NodeRendererFactory[] */
    public function getNodeRendererFactories(): array
    {
        return [
            Nodes\AnchorNode::class => new CallableNodeRendererFactory(
                fn (Nodes\AnchorNode $node): HTML\Renderers\AnchorNodeRenderer => new HTML\Renderers\AnchorNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\CodeNode::class => new CallableNodeRendererFactory(
                fn (Nodes\CodeNode $node): HTML\Renderers\CodeNodeRenderer => new HTML\Renderers\CodeNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\DefinitionListNode::class => new CallableNodeRendererFactory(
                fn (Nodes\DefinitionListNode $node): HTML\Renderers\DefinitionListNodeRenderer => new HTML\Renderers\DefinitionListNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\FieldListNode::class => new CallableNodeRendererFactory(
                fn (Nodes\FieldListNode $node): HTML\Renderers\FieldListNodeRenderer => new HTML\Renderers\FieldListNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\FigureNode::class => new CallableNodeRendererFactory(
                fn (Nodes\FigureNode $node): HTML\Renderers\FigureNodeRenderer => new HTML\Renderers\FigureNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\ImageNode::class => new CallableNodeRendererFactory(
                fn (Nodes\ImageNode $node): HTML\Renderers\ImageNodeRenderer => new HTML\Renderers\ImageNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\ListNode::class => new CallableNodeRendererFactory(
                fn (Nodes\ListNode $node): HTML\Renderers\ListNodeRenderer => new HTML\Renderers\ListNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\MetaNode::class => new CallableNodeRendererFactory(
                fn (Nodes\MetaNode $node): HTML\Renderers\MetaNodeRenderer => new HTML\Renderers\MetaNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\ParagraphNode::class => new CallableNodeRendererFactory(
                fn (Nodes\ParagraphNode $node): HTML\Renderers\ParagraphNodeRenderer => new HTML\Renderers\ParagraphNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\QuoteNode::class => new CallableNodeRendererFactory(
                fn (Nodes\QuoteNode $node): HTML\Renderers\QuoteNodeRenderer => new HTML\Renderers\QuoteNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\SeparatorNode::class => new CallableNodeRendererFactory(
                fn (Nodes\SeparatorNode $node): HTML\Renderers\SeparatorNodeRenderer => new HTML\Renderers\SeparatorNodeRenderer(
                    $this->templateRenderer
                )
            ),
            Nodes\TableNode::class => new CallableNodeRendererFactory(
                fn (Nodes\TableNode $node): HTML\Renderers\TableNodeRenderer => new HTML\Renderers\TableNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\TitleNode::class => new CallableNodeRendererFactory(
                fn (Nodes\TitleNode $node): HTML\Renderers\TitleNodeRenderer => new HTML\Renderers\TitleNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\TocNode::class => new CallableNodeRendererFactory(
                fn (Nodes\TocNode $node): HTML\Renderers\TocNodeRenderer => new HTML\Renderers\TocNodeRenderer(
                    $node->getEnvironment(),
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\ContentsNode::class => new CallableNodeRendererFactory(
                fn (Nodes\ContentsNode $node): HTML\Renderers\ContentsNodeRenderer => new HTML\Renderers\ContentsNodeRenderer(
                    $node->getEnvironment(),
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\DocumentNode::class => new CallableNodeRendererFactory(
                fn (Nodes\DocumentNode $node): HTML\Renderers\DocumentNodeRenderer => new HTML\Renderers\DocumentNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\SpanNode::class => new CallableNodeRendererFactory(
                static fn (Nodes\SpanNode $node): HTML\Renderers\SpanNodeRenderer => new HTML\Renderers\SpanNodeRenderer(
                    $node->getEnvironment(),
                    $node,
                )
            ),
            Nodes\CallableNode::class => new CallableNodeRendererFactory(
                static fn (Nodes\CallableNode $node): Renderers\CallableNodeRenderer => new Renderers\CallableNodeRenderer(
                    $node
                )
            ),
            Nodes\SectionBeginNode::class => new CallableNodeRendererFactory(
                fn (Nodes\SectionBeginNode $node): HTML\Renderers\SectionBeginNodeRenderer => new HTML\Renderers\SectionBeginNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\SectionEndNode::class => new CallableNodeRendererFactory(
                fn (Nodes\SectionEndNode $node): HTML\Renderers\SectionEndNodeRenderer => new HTML\Renderers\SectionEndNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
        ];
    }
}
