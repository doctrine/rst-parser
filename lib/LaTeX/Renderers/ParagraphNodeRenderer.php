<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class ParagraphNodeRenderer implements NodeRenderer
{
    private ParagraphNode $paragraphNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(ParagraphNode $paragraphNode, TemplateRenderer $templateRenderer)
    {
        $this->paragraphNode    = $paragraphNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('paragraph.tex.twig', [
            'paragraphNode' => $this->paragraphNode,
        ]);
    }
}
