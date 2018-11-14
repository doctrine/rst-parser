<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Renderers\FormatListRenderer;

class ListRenderer implements FormatListRenderer
{
    public function createElement(string $text, string $prefix) : string
    {
        $class = '';

        if ($prefix === '-') {
            $class = ' class="dash"';
        }

        return '<li' . $class . '>' . $text . '</li>';
    }

    /**
     * @return string[]
     */
    public function createList(bool $ordered) : array
    {
        $keyword = $ordered ? 'ol' : 'ul';

        return ['<' . $keyword . '>', '</' . $keyword . '>'];
    }
}
