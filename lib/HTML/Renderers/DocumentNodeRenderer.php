<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Renderers\DocumentNodeRenderer as BaseDocumentRender;
use Doctrine\RST\Renderers\FullDocumentNodeRenderer;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;
use Gajus\Dindent\Indenter;

class DocumentNodeRenderer implements NodeRenderer, FullDocumentNodeRenderer
{
    /** @var DocumentNode */
    private $document;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(DocumentNode $document, TemplateRenderer $templateRenderer)
    {
        $this->document         = $document;
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return (new BaseDocumentRender($this->document))->render();
    }

    public function renderDocument() : string
    {
        $headerNodes = '';

        foreach ($this->document->getHeaderNodes() as $node) {
            $headerNodes .= $node->render() . "\n";
        }

        $html = $this->templateRenderer->render('document.html.twig', [
            'headerNodes' => $headerNodes,
            'bodyNodes' => $this->render(),
        ]);

        if ($this->document->getConfiguration()->getIndentHTML()) {
            return $this->indentHTML($html);
        }

        return $html;
    }

    private function indentHTML(string $html) : string
    {
        return (new Indenter())->indent($html);
    }
}
