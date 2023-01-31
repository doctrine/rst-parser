<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Nodes\DocumentNode;

final class DocumentNodeRenderer implements NodeRenderer
{
    private DocumentNode $document;

    public function __construct(DocumentNode $document)
    {
        $this->document = $document;
    }

    public function render(): string
    {
        $document = '';
        foreach ($this->document->getNodes() as $node) {
            $document .= $node->render() . "\n";
        }

        return $document;
    }
}
