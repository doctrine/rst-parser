<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

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
        return "\\begin{quotation}\n" . $this->quoteNode->getValue()->render() . "\n\\end{quotation}\n";
    }
}
