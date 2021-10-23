<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

use function htmlspecialchars;
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

    public function literal(string $text): string
    {
        return $this->templateRenderer->render('literal.html.twig', ['text' => $text]);
    }

    /**
     * @param mixed[] $attributes
     */
    public function link(?string $url, string $title, array $attributes = []): string
    {
        $url = (string) $url;

        return $this->templateRenderer->render('link.html.twig', [
            'url' => $this->environment->generateUrl($url),
            'title' => $title,
            'attributes' => $attributes,
        ]);
    }

    public function escape(string $span): string
    {
        return htmlspecialchars($span);
    }

    /**
     * @param mixed[] $value
     */
    public function reference(ResolvedReference $reference, array $value): string
    {
        $text = (bool) $value['text'] ? $value['text'] : ($reference->getTitle() ?? '');
        $text = trim($text);

        // reference to another document
        if ($reference->getUrl() !== null) {
            $url = $reference->getUrl();

            if ($value['anchor'] !== null) {
                $url .= '#' . $value['anchor'];
            }

            $link = $this->link($url, $text, $reference->getAttributes());

        // reference to anchor in existing document
        } elseif ($value['url'] !== null) {
            $url = $this->environment->getLink($value['url']);

            $link = $this->link($url, $text, $reference->getAttributes());
        } else {
            $link = $this->link('#', $text . ' (unresolved reference)', $reference->getAttributes());
        }

        return $link;
    }
}
