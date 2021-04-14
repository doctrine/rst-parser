<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\SectionBeginNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class SectionBeginNodeRenderer implements NodeRenderer
{
    /** @var SectionBeginNode */
    private $sectionBeginNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(SectionBeginNode $sectionBeginNode, TemplateRenderer $templateRenderer)
    {
        $this->sectionBeginNode = $sectionBeginNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('section-begin.html.twig', [
            'sectionBeginNode' => $this->sectionBeginNode,
        ]);
    }
}
