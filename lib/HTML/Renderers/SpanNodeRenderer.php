<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

use function htmlspecialchars;

use const ENT_COMPAT;

final class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    private TemplateRenderer $templateRenderer;

    public function __construct(
        Environment $environment,
        SpanNode $span,
        TemplateRenderer $templateRenderer
    ) {
        parent::__construct($environment, $span);

        $this->templateRenderer = $templateRenderer;
    }

    public function emphasis(string $text): string
    {
        return $this->templateRenderer->render('emphasis.html.twig', ['text' => $text]);
    }

    public function strongEmphasis(string $text): string
    {
        return $this->templateRenderer->render('strong-emphasis.html.twig', ['text' => $text]);
    }

    public function nbsp(): string
    {
        return $this->templateRenderer->render('nbsp.html.twig');
    }

    public function br(): string
    {
        return $this->templateRenderer->render('br.html.twig');
    }

    public function escape(string $span): string
    {
        return htmlspecialchars($span, ENT_COMPAT);
    }
}
