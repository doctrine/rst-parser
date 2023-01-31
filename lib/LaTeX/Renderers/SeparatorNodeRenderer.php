<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class SeparatorNodeRenderer implements NodeRenderer
{
    private TemplateRenderer $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('separator.tex.twig');
    }
}
