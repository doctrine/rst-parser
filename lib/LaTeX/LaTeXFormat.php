<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX;

use Doctrine\RST\Directives\DirectiveFactory;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\LaTeX;
use Doctrine\RST\LaTeX\Directives\FormatDirectiveFactory;
use Doctrine\RST\Nodes;
use Doctrine\RST\Renderers;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;

final class LaTeXFormat implements Format
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
        return Format::LATEX;
    }

    public function getTemplateRenderer(): TemplateRenderer
    {
        return $this->templateRenderer;
    }

    public function setTemplateRenderer(TemplateRenderer $templateRenderer): void
    {
        $this->templateRenderer = $templateRenderer;
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
                fn (Nodes\AnchorNode $node): LaTeX\Renderers\AnchorNodeRenderer => new LaTeX\Renderers\AnchorNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\CodeNode::class => new CallableNodeRendererFactory(
                fn (Nodes\CodeNode $node): LaTeX\Renderers\CodeNodeRenderer => new LaTeX\Renderers\CodeNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\ImageNode::class => new CallableNodeRendererFactory(
                fn (Nodes\ImageNode $node): LaTeX\Renderers\ImageNodeRenderer => new LaTeX\Renderers\ImageNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\ListNode::class => new CallableNodeRendererFactory(
                fn (Nodes\ListNode $node): LaTeX\Renderers\ListNodeRenderer => new LaTeX\Renderers\ListNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\MetaNode::class => new CallableNodeRendererFactory(
                fn (Nodes\MetaNode $node): LaTeX\Renderers\MetaNodeRenderer => new LaTeX\Renderers\MetaNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\ParagraphNode::class => new CallableNodeRendererFactory(
                fn (Nodes\ParagraphNode $node): LaTeX\Renderers\ParagraphNodeRenderer => new LaTeX\Renderers\ParagraphNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\QuoteNode::class => new CallableNodeRendererFactory(
                fn (Nodes\QuoteNode $node): LaTeX\Renderers\QuoteNodeRenderer => new LaTeX\Renderers\QuoteNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\SeparatorNode::class => new CallableNodeRendererFactory(
                fn (Nodes\SeparatorNode $node): LaTeX\Renderers\SeparatorNodeRenderer => new LaTeX\Renderers\SeparatorNodeRenderer(
                    $this->templateRenderer
                )
            ),
            Nodes\TableNode::class => new CallableNodeRendererFactory(
                static fn (Nodes\TableNode $node): LaTeX\Renderers\TableNodeRenderer => new LaTeX\Renderers\TableNodeRenderer(
                    $node
                )
            ),
            Nodes\TitleNode::class => new CallableNodeRendererFactory(
                fn (Nodes\TitleNode $node): LaTeX\Renderers\TitleNodeRenderer => new LaTeX\Renderers\TitleNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\TocNode::class => new CallableNodeRendererFactory(
                fn (Nodes\TocNode $node): LaTeX\Renderers\TocNodeRenderer => new LaTeX\Renderers\TocNodeRenderer(
                    $node->getEnvironment(),
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\DocumentNode::class => new CallableNodeRendererFactory(
                fn (Nodes\DocumentNode $node): LaTeX\Renderers\DocumentNodeRenderer => new LaTeX\Renderers\DocumentNodeRenderer(
                    $node,
                    $this->templateRenderer
                )
            ),
            Nodes\SpanNode::class => new CallableNodeRendererFactory(
                static fn (Nodes\SpanNode $node): LaTeX\Renderers\SpanNodeRenderer => new LaTeX\Renderers\SpanNodeRenderer(
                    $node->getEnvironment(),
                    $node
                )
            ),
            Nodes\CallableNode::class => new CallableNodeRendererFactory(
                static fn (Nodes\CallableNode $node): Renderers\CallableNodeRenderer => new Renderers\CallableNodeRenderer(
                    $node
                )
            ),
        ];
    }
}
