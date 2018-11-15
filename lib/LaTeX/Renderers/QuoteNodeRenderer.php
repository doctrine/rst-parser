<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class QuoteNodeRenderer implements NodeRenderer
{
    /** @var QuoteNode */
    private $quoteNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(QuoteNode $quoteNode, TemplateRenderer $templateRenderer)
    {
        $this->quoteNode        = $quoteNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return $this->templateRenderer->render('quote.tex.twig', [
            'quoteNode' => $this->quoteNode,
        ]);
    }
}
