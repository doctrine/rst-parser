<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\TableNode as Base;
use Doctrine\RST\Span;
use function count;
use function sprintf;

class TableNode extends Base
{
    protected function doRender() : string
    {
        $html = '<table class="table table-bordered">';

        if (count($this->headers) !== 0) {
            $html .= '<thead><tr>';

            foreach ($this->headers as $k => $isHeader) {
                if (! isset($this->data[$k])) {
                    continue;
                }

                /** @var Span $col */
                foreach ($this->data[$k] as &$col) {
                    $html .= sprintf('<th>%s</th>', $col->render());
                }

                unset($this->data[$k]);
            }

            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';

        foreach ($this->data as $k => &$row) {
            if ($row === []) {
                continue;
            }

            $html .= '<tr>';

            /** @var Span $col */
            foreach ($row as &$col) {
                $html .= sprintf('<td>%s</td>', $col->render());
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
