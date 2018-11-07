<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML;

use Doctrine\RST\Directive;
use Doctrine\RST\Formats\Format;
use Doctrine\RST\HTML;
use Doctrine\RST\Nodes;
use Doctrine\RST\Renderers;
use Doctrine\RST\Renderers\CallableNodeRendererFactory;
use Doctrine\RST\Renderers\NodeRendererFactory;

class HTMLFormat implements Format
{
    public function getFileExtension() : string
    {
        return Format::HTML;
    }

    /**
     * @return Directive[]
     */
    public function getDirectives() : array
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
                    return new HTML\Renderers\AnchorNodeRenderer($node);
                }
            ),
            Nodes\CodeNode::class => new CallableNodeRendererFactory(
                static function (Nodes\CodeNode $node) {
                    return new HTML\Renderers\CodeNodeRenderer($node);
                }
            ),
            Nodes\DefinitionListNode::class => new CallableNodeRendererFactory(
                static function (Nodes\DefinitionListNode $node) {
                    return new HTML\Renderers\DefinitionListNodeRenderer($node);
                }
            ),
            Nodes\FigureNode::class => new CallableNodeRendererFactory(
                static function (Nodes\FigureNode $node) {
                    return new HTML\Renderers\FigureNodeRenderer($node);
                }
            ),
            Nodes\ImageNode::class => new CallableNodeRendererFactory(
                static function (Nodes\ImageNode $node) {
                    return new HTML\Renderers\ImageNodeRenderer($node);
                }
            ),
            Nodes\ListNode::class => new CallableNodeRendererFactory(
                static function (Nodes\ListNode $node) {
                    return new Renderers\ListNodeRenderer($node, new HTML\Renderers\ListRenderer());
                }
            ),
            Nodes\MetaNode::class => new CallableNodeRendererFactory(
                static function (Nodes\MetaNode $node) {
                    return new HTML\Renderers\MetaNodeRenderer($node);
                }
            ),
            Nodes\ParagraphNode::class => new CallableNodeRendererFactory(
                static function (Nodes\ParagraphNode $node) {
                    return new HTML\Renderers\ParagraphNodeRenderer($node);
                }
            ),
            Nodes\QuoteNode::class => new CallableNodeRendererFactory(
                static function (Nodes\QuoteNode $node) {
                    return new HTML\Renderers\QuoteNodeRenderer($node);
                }
            ),
            Nodes\SeparatorNode::class => new CallableNodeRendererFactory(
                static function (Nodes\SeparatorNode $node) {
                    return new HTML\Renderers\SeparatorNodeRenderer();
                }
            ),
            Nodes\TableNode::class => new CallableNodeRendererFactory(
                static function (Nodes\TableNode $node) {
                    return new HTML\Renderers\TableNodeRenderer($node);
                }
            ),
            Nodes\TitleNode::class => new CallableNodeRendererFactory(
                static function (Nodes\TitleNode $node) {
                    return new HTML\Renderers\TitleNodeRenderer($node);
                }
            ),
            Nodes\TocNode::class => new CallableNodeRendererFactory(
                static function (Nodes\TocNode $node) {
                    return new HTML\Renderers\TocNodeRenderer($node->getEnvironment(), $node);
                }
            ),
            Nodes\DocumentNode::class => new CallableNodeRendererFactory(
                static function (Nodes\DocumentNode $node) {
                    return new HTML\Renderers\DocumentNodeRenderer($node);
                }
            ),
            Nodes\SpanNode::class => new CallableNodeRendererFactory(
                static function (Nodes\SpanNode $node) {
                    return new HTML\Renderers\SpanNodeRenderer($node->getEnvironment(), $node);
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
