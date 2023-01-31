<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use LogicException;

use function sprintf;

final class TableNodeRenderer implements NodeRenderer
{
    private TableNode $tableNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(TableNode $tableNode, TemplateRenderer $templateRenderer)
    {
        $this->tableNode        = $tableNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $headers = $this->tableNode->getHeaders();
        $rows    = $this->tableNode->getData();

        $tableHeaderRows = [];

        foreach ($headers as $k => $isHeader) {
            if ($isHeader === false) {
                continue;
            }

            if (! isset($rows[$k])) {
                throw new LogicException(sprintf('Row "%d" should be a header, but that row does not exist.', $k));
            }

            $tableHeaderRows[] = $rows[$k];
            unset($rows[$k]);
        }

        return $this->templateRenderer->render('table.html.twig', [
            'tableNode' => $this->tableNode,
            'tableHeaderRows' => $tableHeaderRows,
            'tableRows' => $rows,
        ]);
    }
}
