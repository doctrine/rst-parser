<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\AnchorNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class AnchorNodeRenderer implements NodeRenderer
{
    /** @var AnchorNode */
    private $anchorNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(AnchorNode $anchorNode, TemplateRenderer $templateRenderer)
    {
        $this->anchorNode       = $anchorNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('anchor.tex.twig', [
            'anchorNode' => $this->anchorNode,
        ]);
    }
}
