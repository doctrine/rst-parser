<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\ListNode as Base;

class ListNode extends Base
{
    protected function createElement(string $text, string $prefix) : string
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
    protected function createList(bool $ordered) : array
    {
        $keyword = $ordered ? 'ol' : 'ul';

        return ['<' . $keyword . '>', '</' . $keyword . '>'];
    }
}
