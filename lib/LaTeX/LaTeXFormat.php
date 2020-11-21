<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\LaTeX;
use Doctrine\RST\Nodes;
use Doctrine\RST\Renderers;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;

class LaTeXFormat implements Format
{
    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function getFileExtension(): string
    {
        return Format::LATEX;
    }

    /**
     * @return Directive[]
     */
    public function getDirectives(): array
    {
        return [
            new LaTeX\Directives\LaTeXMain(),
            new LaTeX\Directives\Image(),
            new LaTeX\Directives\Meta(),
            new LaTeX\Directives\Stylesheet(),
            new LaTeX\Directives\Title(),
            new LaTeX\Directives\Url(),
            new LaTeX\Directives\Wrap('note'),
        ];
    }

    /**
     * @return NodeRendererFactory[]
     */
    public function getNodeRendererFactories(): array
    {
        return [
            Nodes\AnchorNode::class => new CallableNodeRendererFactory(
                function (Nodes\AnchorNode $node): LaTeX\Renderers\AnchorNodeRenderer {
                    return new LaTeX\Renderers\AnchorNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\CodeNode::class => new CallableNodeRendererFactory(
                function (Nodes\CodeNode $node): LaTeX\Renderers\CodeNodeRenderer {
                    return new LaTeX\Renderers\CodeNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\ImageNode::class => new CallableNodeRendererFactory(
                function (Nodes\ImageNode $node): LaTeX\Renderers\ImageNodeRenderer {
                    return new LaTeX\Renderers\ImageNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\ListNode::class => new CallableNodeRendererFactory(
                function (Nodes\ListNode $node): Renderers\ListNodeRenderer {
                    return new Renderers\ListNodeRenderer(
                        $node,
                        new LaTeX\Renderers\ListRenderer($node, $this->templateRenderer)
                    );
                }
            ),
            Nodes\MetaNode::class => new CallableNodeRendererFactory(
                function (Nodes\MetaNode $node): LaTeX\Renderers\MetaNodeRenderer {
                    return new LaTeX\Renderers\MetaNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\ParagraphNode::class => new CallableNodeRendererFactory(
                function (Nodes\ParagraphNode $node): LaTeX\Renderers\ParagraphNodeRenderer {
                    return new LaTeX\Renderers\ParagraphNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\QuoteNode::class => new CallableNodeRendererFactory(
                function (Nodes\QuoteNode $node): LaTeX\Renderers\QuoteNodeRenderer {
                    return new LaTeX\Renderers\QuoteNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\SeparatorNode::class => new CallableNodeRendererFactory(
                function (Nodes\SeparatorNode $node): LaTeX\Renderers\SeparatorNodeRenderer {
                    return new LaTeX\Renderers\SeparatorNodeRenderer(
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\TableNode::class => new CallableNodeRendererFactory(
                static function (Nodes\TableNode $node): LaTeX\Renderers\TableNodeRenderer {
                    return new LaTeX\Renderers\TableNodeRenderer(
                        $node
                    );
                }
            ),
            Nodes\TitleNode::class => new CallableNodeRendererFactory(
                function (Nodes\TitleNode $node): LaTeX\Renderers\TitleNodeRenderer {
                    return new LaTeX\Renderers\TitleNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\TocNode::class => new CallableNodeRendererFactory(
                function (Nodes\TocNode $node): LaTeX\Renderers\TocNodeRenderer {
                    return new LaTeX\Renderers\TocNodeRenderer(
                        $node->getEnvironment(),
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\DocumentNode::class => new CallableNodeRendererFactory(
                function (Nodes\DocumentNode $node): LaTeX\Renderers\DocumentNodeRenderer {
                    return new LaTeX\Renderers\DocumentNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\SpanNode::class => new CallableNodeRendererFactory(
                function (Nodes\SpanNode $node): LaTeX\Renderers\SpanNodeRenderer {
                    return new LaTeX\Renderers\SpanNodeRenderer(
                        $node->getEnvironment(),
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\CallableNode::class => new CallableNodeRendererFactory(
                static function (Nodes\CallableNode $node): Renderers\CallableNodeRenderer {
                    return new Renderers\CallableNodeRenderer(
                        $node
                    );
                }
            ),
        ];
    }
}
