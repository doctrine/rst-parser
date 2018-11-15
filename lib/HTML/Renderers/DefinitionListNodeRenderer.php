<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\DefinitionListNode;
use Doctrine\RST\Parser\DefinitionList;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class DefinitionListNodeRenderer implements NodeRenderer
{
    /** @var DefinitionList */
    private $definitionList;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(DefinitionListNode $definitionListNode, TemplateRenderer $templateRenderer)
    {
        $this->definitionList   = $definitionListNode->getDefinitionList();
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return $this->templateRenderer->render('definition-list.html.twig', [
            'definitionList' => $this->definitionList,
        ]);
    }
}
