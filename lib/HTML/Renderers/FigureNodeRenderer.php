<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\FigureNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class FigureNodeRenderer implements NodeRenderer
{
    private FigureNode $figureNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(FigureNode $figureNode, TemplateRenderer $templateRenderer)
    {
        $this->figureNode       = $figureNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('figure.html.twig', [
            'figureNode' => $this->figureNode,
        ]);
    }
}
