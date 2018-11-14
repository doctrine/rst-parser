<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Renderers\FormatListRenderer;

class ListRenderer implements FormatListRenderer
{
    public function createElement(string $text, string $prefix) : string
    {
        return '\item ' . $text;
    }

    /**
     * @return string[]
     */
    public function createList(bool $ordered) : array
    {
        $keyword = $ordered ? 'enumerate': 'itemize';

        return ['\\begin{' . $keyword . '}', '\\end{' . $keyword . '}'];
    }
}
