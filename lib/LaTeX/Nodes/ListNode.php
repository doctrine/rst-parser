<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\ListNode as Base;

class ListNode extends Base
{
    protected function createElement(string $text, string $prefix) : string
    {
        return '\item ' . $text;
    }

    /**
     * @return string[]
     */
    protected function createList(bool $ordered) : array
    {
        $keyword = $ordered ? 'enumerate': 'itemize';

        return ['\\begin{' . $keyword . '}', '\\end{' . $keyword . '}'];
    }
}
