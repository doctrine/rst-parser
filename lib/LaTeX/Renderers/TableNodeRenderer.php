<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Renderers\NodeRenderer;

use function count;
use function implode;
use function max;

class TableNodeRenderer implements NodeRenderer
{
    /** @var TableNode */
    private $tableNode;

    public function __construct(TableNode $tableNode)
    {
        $this->tableNode = $tableNode;
    }

    public function render(): string
    {
        $cols = 0;

        $rows = [];
        foreach ($this->tableNode->getData() as $row) {
            $rowTex = '';
            $cols   = max($cols, count($row->getColumns()));

            foreach ($row->getColumns() as $n => $col) {
                $rowTex .= $col->render();

                if ((int) $n + 1 >= count($row->getColumns())) {
                    continue;
                }

                $rowTex .= ' & ';
            }

            $rowTex .= ' \\\\' . "\n";
            $rows[]  = $rowTex;
        }

        $aligns = [];
        for ($i = 0; $i < $cols; $i++) {
            $aligns[] = 'l';
        }

        $aligns = '|' . implode('|', $aligns) . '|';
        $rows   = "\\hline\n" . implode("\\hline\n", $rows) . "\\hline\n";

        return "\\begin{tabular}{" . $aligns . "}\n" . $rows . "\n\\end{tabular}\n";
    }
}
