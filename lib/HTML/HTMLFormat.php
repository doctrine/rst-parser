<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\HTML;
use Doctrine\RST\Nodes;
use Doctrine\RST\Renderers;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;
use Doctrine\RST\Templates\TemplateRenderer;
use Doctrine\RST\TextRoles\DefinitionTextRole;
use Doctrine\RST\TextRoles\TextRole;
use Doctrine\RST\TextRoles\WrapperTextRole;

final class HTMLFormat implements Format
{
    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function getFileExtension(): string
    {
        return Format::HTML;
    }

    /** @return Directive[] */
    public function getDirectives(): array
    {
        return [
            new HTML\Directives\Image(),
            new HTML\Directives\Figure(),
            new HTML\Directives\Meta(),
            new HTML\Directives\Stylesheet(),
            new HTML\Directives\Title(),
            new HTML\Directives\Url(),
            new HTML\Directives\Div(),
            new HTML\Directives\Wrap('note'),
            new HTML\Directives\ClassDirective(),
        ];
    }

    /** @return TextRole[] */
    public function getTextRoles(): array
    {
        return [
            new WrapperTextRole('aspect', '<em class="aspect">%s</em>'),
            new WrapperTextRole('command', '<strong class="command">%s</strong>'),
            new WrapperTextRole('dfn', '<em class="dfn">%s</em>'),
            new WrapperTextRole('file', '<span class="pre file">%s</span>'),
            new WrapperTextRole('guilabel', '<span class="guilabel">%s</span>'),
            new WrapperTextRole('kbd', '<kbd class="kbd docutils literal notranslate">%s</kbd>'),
            new WrapperTextRole('mailheader', '<em class="mailheader">%s</em>'),
            new WrapperTextRole('math', '<math>%s</math>'),
            new WrapperTextRole('emphasis', '<em>%s</em>'),
            new WrapperTextRole('literal', '<span class="pre">%s</span>'),
            new WrapperTextRole('strong', '<strong>%s</strong>'),
            new WrapperTextRole('subscript', '<sub>%s</sub>', ['sub']),
            new WrapperTextRole('superscript', '<sup>%s</sup>', ['sup']),
            new WrapperTextRole('title-reference', '<cite>%s</cite>', ['t', 'title']),
            new DefinitionTextRole('abbreviation', '<abbr title="%2$s">%1$s</abbr>', ['abbr']),
        ];
    }

    /** @return NodeRendererFactory[] */
    public function getNodeRendererFactories(): array
    {
        return [
            Nodes\AnchorNode::class => new CallableNodeRendererFactory(
                function (Nodes\AnchorNode $node): HTML\Renderers\AnchorNodeRenderer {
                    return new HTML\Renderers\AnchorNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\CodeNode::class => new CallableNodeRendererFactory(
                function (Nodes\CodeNode $node): HTML\Renderers\CodeNodeRenderer {
                    return new HTML\Renderers\CodeNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\DefinitionListNode::class => new CallableNodeRendererFactory(
                function (Nodes\DefinitionListNode $node): HTML\Renderers\DefinitionListNodeRenderer {
                    return new HTML\Renderers\DefinitionListNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\FigureNode::class => new CallableNodeRendererFactory(
                function (Nodes\FigureNode $node): HTML\Renderers\FigureNodeRenderer {
                    return new HTML\Renderers\FigureNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\ImageNode::class => new CallableNodeRendererFactory(
                function (Nodes\ImageNode $node): HTML\Renderers\ImageNodeRenderer {
                    return new HTML\Renderers\ImageNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\ListNode::class => new CallableNodeRendererFactory(
                function (Nodes\ListNode $node): HTML\Renderers\ListNodeRenderer {
                    return new HTML\Renderers\ListNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\MetaNode::class => new CallableNodeRendererFactory(
                function (Nodes\MetaNode $node): HTML\Renderers\MetaNodeRenderer {
                    return new HTML\Renderers\MetaNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\ParagraphNode::class => new CallableNodeRendererFactory(
                function (Nodes\ParagraphNode $node): HTML\Renderers\ParagraphNodeRenderer {
                    return new HTML\Renderers\ParagraphNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\QuoteNode::class => new CallableNodeRendererFactory(
                function (Nodes\QuoteNode $node): HTML\Renderers\QuoteNodeRenderer {
                    return new HTML\Renderers\QuoteNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\SeparatorNode::class => new CallableNodeRendererFactory(
                function (Nodes\SeparatorNode $node): HTML\Renderers\SeparatorNodeRenderer {
                    return new HTML\Renderers\SeparatorNodeRenderer(
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\TableNode::class => new CallableNodeRendererFactory(
                function (Nodes\TableNode $node): HTML\Renderers\TableNodeRenderer {
                    return new HTML\Renderers\TableNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\TitleNode::class => new CallableNodeRendererFactory(
                function (Nodes\TitleNode $node): HTML\Renderers\TitleNodeRenderer {
                    return new HTML\Renderers\TitleNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\TocNode::class => new CallableNodeRendererFactory(
                function (Nodes\TocNode $node): HTML\Renderers\TocNodeRenderer {
                    return new HTML\Renderers\TocNodeRenderer(
                        $node->getEnvironment(),
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\ContentsNode::class => new CallableNodeRendererFactory(
                function (Nodes\ContentsNode $node): HTML\Renderers\ContentsNodeRenderer {
                    return new HTML\Renderers\ContentsNodeRenderer(
                        $node->getEnvironment(),
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\DocumentNode::class => new CallableNodeRendererFactory(
                function (Nodes\DocumentNode $node): HTML\Renderers\DocumentNodeRenderer {
                    return new HTML\Renderers\DocumentNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\SpanNode::class => new CallableNodeRendererFactory(
                function (Nodes\SpanNode $node): HTML\Renderers\SpanNodeRenderer {
                    return new HTML\Renderers\SpanNodeRenderer(
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
            Nodes\SectionBeginNode::class => new CallableNodeRendererFactory(
                function (Nodes\SectionBeginNode $node): HTML\Renderers\SectionBeginNodeRenderer {
                    return new HTML\Renderers\SectionBeginNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
            Nodes\SectionEndNode::class => new CallableNodeRendererFactory(
                function (Nodes\SectionEndNode $node): HTML\Renderers\SectionEndNodeRenderer {
                    return new HTML\Renderers\SectionEndNodeRenderer(
                        $node,
                        $this->templateRenderer
                    );
                }
            ),
        ];
    }
}
