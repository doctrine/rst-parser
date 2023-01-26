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
        SpanNode $span,
        TemplateRenderer $templateRenderer
    ) {
        parent::__construct($environment, $span, new LinkRenderer($environment));

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

    public function br(): string
    {
        return $this->templateRenderer->render('br.tex.twig');
    }

    public function literal(string $text): string
    {
        return $this->templateRenderer->render('literal.tex.twig', ['text' => $text]);
    }

    public function interpretedText(string $text): string
    {
        return $this->templateRenderer->render('interpreted-text.tex.twig', ['text' => $text]);
    }

    public function escape(string $span): string
    {
        return $span;
    }
}
