<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    private TemplateRenderer $templateRenderer;

    public function __construct(
        Environment $environment,
        SpanNode $spanNode,
        TemplateRenderer $templateRenderer
    ) {
        parent::__construct($environment, $spanNode);

        $this->templateRenderer = $templateRenderer;
    }

    public function emphasis(string $text): string
    {
        return $this->templateRenderer->render('emphasis.tex.twig', ['text' => $text]);
    }

    public function strongEmphasis(string $text): string
    {
        return $this->templateRenderer->render('strong-emphasis.tex.twig', ['text' => $text]);
    }

    public function nbsp(): string
    {
        return $this->templateRenderer->render('nbsp.tex.twig');
    }

    public function escape(string $span): string
    {
        return $span;
    }
}
