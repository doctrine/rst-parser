<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\TableNode as Base;

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
