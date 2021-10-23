<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

use function is_string;
use function substr;
use function trim;

final class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    /** @var TemplateRenderer */
    private $templateRenderer;

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

    /**
     * @param mixed[] $attributes
     */
    public function link(?string $url, string $title, array $attributes = []): string
    {
        $type = 'href';

        if (is_string($url) && $url !== '' && $url[0] === '#') {
            $type = 'ref';

            $url = substr($url, 1);
            $url = $url !== '' ? '#' . $url : '';
            $url = $this->environment->getUrl() . $url;
        }

        return $this->templateRenderer->render('link.tex.twig', [
            'type' => $type,
            'url' => $url,
            'title' => $title,
            'attributes' => $attributes,
        ]);
    }

    public function escape(string $span): string
    {
        return $span;
    }

    /**
     * @param mixed[] $value
     */
    public function reference(ResolvedReference $reference, array $value): string
    {
        $text = (bool) $value['text'] ? $value['text'] : $reference->getTitle();
        $url  = $reference->getUrl();

        if ($value['anchor'] !== '') {
            $url .= $value['anchor'];
        }

        if ($text === null) {
            $text = '';
        }

        if ($url === null) {
            $url = '';
        }

        return $this->link($url, trim($text));
    }
}
