<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Renderers\FormatListRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use RuntimeException;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function explode;

class ListRenderer implements FormatListRenderer
{
    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function createElement(string $text, string $prefix) : string
    {
        return $this->templateRenderer->render('list-item.html.twig', [
            'text' => $text,
            'prefix' => $prefix,
        ]);
    }

    /**
     * @return string[]
     */
    public function createList(bool $ordered) : array
    {
        $lines = explode("\n", $this->templateRenderer->render('list.html.twig', [
            'keyword' => $ordered ? 'ol' : 'ul',
        ]));

        $lines = array_map('trim', $lines);

        $lines = array_values(array_filter($lines, static function (string $line) {
            return $line !== '';
        }));

        if (count($lines) !== 2) {
            throw new RuntimeException('list.html.twig must contain only 2 lines');
        }

        return $lines;
    }
}
