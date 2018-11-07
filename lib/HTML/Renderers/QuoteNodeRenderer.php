<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Renderers\NodeRenderer;

class QuoteNodeRenderer implements NodeRenderer
{
    /** @var QuoteNode */
    private $quoteNode;

    public function __construct(QuoteNode $quoteNode)
    {
        $this->quoteNode = $quoteNode;
    }

    public function render() : string
    {
        return '<blockquote>' . $this->quoteNode->getValue()->render() . '</blockquote>';
    }
}
