<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\AnchorNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class AnchorNodeRenderer implements NodeRenderer
{
    private AnchorNode $anchorNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(AnchorNode $anchorNode, TemplateRenderer $templateRenderer)
    {
        $this->anchorNode       = $anchorNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('anchor.html.twig', [
            'anchorNode' => $this->anchorNode,
        ]);
    }
}
