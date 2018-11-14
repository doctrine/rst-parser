<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Renderers\NodeRenderer;
use function count;
use function sprintf;

class TableNodeRenderer implements NodeRenderer
{
    /** @var TableNode */
    private $tableNode;

    public function __construct(TableNode $tableNode)
    {
        $this->tableNode = $tableNode;
    }

    public function render() : string
    {
        $html = '<table class="table table-bordered">';

        $headers = $this->tableNode->getHeaders();
        $data    = $this->tableNode->getData();

        if (count($headers) !== 0) {
            $html .= '<thead><tr>';

            foreach ($headers as $k => $isHeader) {
                if (! isset($data[$k])) {
                    continue;
                }

                /** @var SpanNode $col */
                foreach ($data[$k] as &$col) {
                    $html .= sprintf('<th>%s</th>', $col->render());
                }

                unset($data[$k]);
            }

            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';

        foreach ($data as $k => &$row) {
            if ($row === []) {
                continue;
            }

            $html .= '<tr>';

            /** @var SpanNode $col */
            foreach ($row as &$col) {
                $html .= sprintf('<td>%s</td>', $col->render());
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
