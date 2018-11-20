<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\SectionEndNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class SectionEndNodeRenderer implements NodeRenderer
{
    /** @var SectionEndNode */
    private $sectionEndNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(SectionEndNode $sectionEndNode, TemplateRenderer $templateRenderer)
    {
        $this->sectionEndNode   = $sectionEndNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return $this->templateRenderer->render('section-end.html.twig', [
            'sectionEndNode' => $this->sectionEndNode,
        ]);
    }
}
