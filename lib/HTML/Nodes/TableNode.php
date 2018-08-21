<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\TableNode as Base;
use Doctrine\RST\Span;

class TableNode extends Base
{
    public function render() : string
    {
        $html = '<table class="table table-bordered">';

        foreach ($this->data as $k => &$row) {
            if ($row === []) {
                continue;
            }

            $html .= '<tr>';

            /** @var Span $col */
            foreach ($row as &$col) {
                $html .= isset($this->headers[$k]) ? '<th>' : '<td>';
                $html .= $col->render();
                $html .= isset($this->headers[$k]) ? '</th>' : '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }
}
