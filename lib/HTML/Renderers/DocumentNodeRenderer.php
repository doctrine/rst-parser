<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Renderers\DocumentNodeRenderer as BaseDocumentRender;
use Doctrine\RST\Renderers\FullDocumentNodeRenderer;
use Doctrine\RST\Renderers\NodeRenderer;

class DocumentNodeRenderer implements NodeRenderer, FullDocumentNodeRenderer
{
    /** @var DocumentNode */
    private $document;

    public function __construct(DocumentNode $document)
    {
        $this->document = $document;
    }

    public function render() : string
    {
        return (new BaseDocumentRender($this->document))->render();
    }

    public function renderDocument() : string
    {
        $document  = "<!DOCTYPE html>\n";
        $document .= "<html>\n";

        $document .= "<head>\n";
        $document .= "<meta charset=\"utf-8\" />\n";

        foreach ($this->document->getHeaderNodes() as $node) {
            $document .= $node->render() . "\n";
        }

        $document .= "</head>\n";

        $document .= "<body>\n";

        $document .= $this->render();

        $document .= "</body>\n";

        $document .= "</html>\n";

        return $document;
    }
}
