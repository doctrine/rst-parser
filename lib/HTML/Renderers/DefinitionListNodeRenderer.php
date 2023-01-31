<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\DefinitionListNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class DefinitionListNodeRenderer implements NodeRenderer
{
    private DefinitionListNode $definitionListNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(DefinitionListNode $definitionListNode, TemplateRenderer $templateRenderer)
    {
        $this->definitionListNode = $definitionListNode;
        $this->templateRenderer   = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('definition-list.html.twig', [
            'definitionListNode' => $this->definitionListNode,
            'definitionList' => $this->definitionListNode->getDefinitionList(),
        ]);
    }
}
