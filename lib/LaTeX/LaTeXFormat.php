<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX;

use Doctrine\RST\Directive;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\LaTeX;
use Doctrine\RST\Nodes;
use Doctrine\RST\Renderers;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;

class LaTeXFormat implements Format
{
    public function getFileExtension() : string
    {
        return Format::LATEX;
    }

    /**
     * @return Directive[]
     */
    public function getDirectives() : array
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
    public function getNodeRendererFactories() : array
    {
        return [
            Nodes\AnchorNode::class => new CallableNodeRendererFactory(
                static function (Nodes\AnchorNode $node) {
                    return new LaTeX\Renderers\AnchorNodeRenderer($node);
                }
            ),
            Nodes\CodeNode::class => new CallableNodeRendererFactory(
                static function (Nodes\CodeNode $node) {
                    return new LaTeX\Renderers\CodeNodeRenderer($node);
                }
            ),
            Nodes\ImageNode::class => new CallableNodeRendererFactory(
                static function (Nodes\ImageNode $node) {
                    return new LaTeX\Renderers\ImageNodeRenderer($node);
                }
            ),
            Nodes\ListNode::class => new CallableNodeRendererFactory(
                static function (Nodes\ListNode $node) {
                    return new Renderers\ListNodeRenderer($node, new LaTeX\Renderers\ListRenderer());
                }
            ),
            Nodes\MetaNode::class => new CallableNodeRendererFactory(
                static function (Nodes\MetaNode $node) {
                    return new LaTeX\Renderers\MetaNodeRenderer();
                }
            ),
            Nodes\ParagraphNode::class => new CallableNodeRendererFactory(
                static function (Nodes\ParagraphNode $node) {
                    return new LaTeX\Renderers\ParagraphNodeRenderer($node);
                }
            ),
            Nodes\QuoteNode::class => new CallableNodeRendererFactory(
                static function (Nodes\QuoteNode $node) {
                    return new LaTeX\Renderers\QuoteNodeRenderer($node);
                }
            ),
            Nodes\SeparatorNode::class => new CallableNodeRendererFactory(
                static function (Nodes\SeparatorNode $node) {
                    return new LaTeX\Renderers\SeparatorNodeRenderer();
                }
            ),
            Nodes\TableNode::class => new CallableNodeRendererFactory(
                static function (Nodes\TableNode $node) {
                    return new LaTeX\Renderers\TableNodeRenderer($node);
                }
            ),
            Nodes\TitleNode::class => new CallableNodeRendererFactory(
                static function (Nodes\TitleNode $node) {
                    return new LaTeX\Renderers\TitleNodeRenderer($node);
                }
            ),
            Nodes\TocNode::class => new CallableNodeRendererFactory(
                static function (Nodes\TocNode $node) {
                    return new LaTeX\Renderers\TocNodeRenderer($node->getEnvironment(), $node);
                }
            ),
            Nodes\DocumentNode::class => new CallableNodeRendererFactory(
                static function (Nodes\DocumentNode $node) {
                    return new LaTeX\Renderers\DocumentNodeRenderer($node);
                }
            ),
            Nodes\SpanNode::class => new CallableNodeRendererFactory(
                static function (Nodes\SpanNode $node) {
                    return new LaTeX\Renderers\SpanNodeRenderer($node->getEnvironment(), $node);
                }
            ),
            Nodes\CallableNode::class => new CallableNodeRendererFactory(
                static function (Nodes\CallableNode $node) {
                    return new Renderers\CallableNodeRenderer($node);
                }
            ),
        ];
    }
}
