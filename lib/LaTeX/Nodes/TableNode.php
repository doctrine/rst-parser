<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\TableNode as Base;
use Doctrine\RST\Span;
use function count;
use function implode;
use function max;

class TableNode extends Base
{
    public function render() : string
    {
        $cols = 0;

        $rows = [];
        foreach ($this->data as &$row) {
            if ($row === []) {
                continue;
            }

            $rowTex = '';
            $cols   = max($cols, count($row));

            /** @var Span $col */
            foreach ($row as $n => &$col) {
                $rowTex .= $col->render();

                if ((int) $n + 1 >= count($row)) {
                    continue;
                }

                $rowTex .= ' & ';
            }

            $rowTex .= ' \\\\' . "\n";
            $rows[]  = $rowTex;
        }

        $aligns = [];
        for ($i=0; $i<$cols; $i++) {
            $aligns[] = 'l';
        }

        $aligns = '|' . implode('|', $aligns) . '|';
        $rows   = "\\hline\n" . implode("\\hline\n", $rows) . "\\hline\n";

        return "\\begin{tabular}{" . $aligns . "}\n" . $rows . "\n\\end{tabular}\n";
    }
}
