<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\MainNode;
use Doctrine\RST\Renderers\DocumentNodeRenderer as BaseDocumentRender;
use Doctrine\RST\Renderers\FullDocumentNodeRenderer;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

use function count;

final class DocumentNodeRenderer implements NodeRenderer, FullDocumentNodeRenderer
{
    private DocumentNode $document;

    private TemplateRenderer $templateRenderer;

    public function __construct(DocumentNode $document, TemplateRenderer $templateRenderer)
    {
        $this->document         = $document;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return (new BaseDocumentRender($this->document))->render();
    }

    public function renderDocument(): string
    {
        return $this->templateRenderer->render('document.tex.twig', [
            'isMain' => $this->isMain(),
            'document' => $this->document,
            'body' => $this->render(),
        ]);
    }

    private function isMain(): bool
    {
        return count($this->document->getNodes(static fn ($node): bool => $node instanceof MainNode)) !== 0;
    }
}
