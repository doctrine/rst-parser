<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML;

use Doctrine\RST\Configuration;
use Doctrine\RST\DefaultNodeFactory;
use Doctrine\RST\Directive;
use Doctrine\RST\Environment as BaseEnvironment;
use Doctrine\RST\Kernel as Base;
use Doctrine\RST\NodeFactory;
use Doctrine\RST\NodeInstantiator;
use Doctrine\RST\NodeTypes;

class Kernel extends Base
{
    /**
     * @return Directive[]
     */
    public function createDirectives() : array
    {
        return [
            new Directives\Image(),
            new Directives\Figure(),
            new Directives\Meta(),
            new Directives\Stylesheet(),
            new Directives\Title(),
            new Directives\Url(),
            new Directives\Div(),
            new Directives\Wrap('note'),
        ];
    }

    public function getFileExtension() : string
    {
        return 'html';
    }

    public function createEnvironment(?Configuration $configuration = null) : BaseEnvironment
    {
        return new Environment($configuration);
    }

    protected function createNodeFactory() : NodeFactory
    {
        return new DefaultNodeFactory(
            new NodeInstantiator(NodeTypes::DOCUMENT, Document::class),
            new NodeInstantiator(NodeTypes::TOC, Nodes\TocNode::class),
            new NodeInstantiator(NodeTypes::TITLE, Nodes\TitleNode::class),
            new NodeInstantiator(NodeTypes::SEPARATOR, Nodes\SeparatorNode::class),
            new NodeInstantiator(NodeTypes::CODE, Nodes\CodeNode::class),
            new NodeInstantiator(NodeTypes::QUOTE, Nodes\QuoteNode::class),
            new NodeInstantiator(NodeTypes::PARAGRAPH, Nodes\ParagraphNode::class),
            new NodeInstantiator(NodeTypes::ANCHOR, Nodes\AnchorNode::class),
            new NodeInstantiator(NodeTypes::LIST, Nodes\ListNode::class),
            new NodeInstantiator(NodeTypes::TABLE, Nodes\TableNode::class),
            new NodeInstantiator(NodeTypes::SPAN, Span::class)
        );
    }
}
