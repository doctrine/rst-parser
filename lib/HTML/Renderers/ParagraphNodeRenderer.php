<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class ParagraphNodeRenderer implements NodeRenderer
{
    /** @var ParagraphNode */
    private $paragraphNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(ParagraphNode $paragraphNode, TemplateRenderer $templateRenderer)
    {
        $this->paragraphNode    = $paragraphNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return $this->templateRenderer->render('paragraph.html.twig', [
            'paragraphNode' => $this->paragraphNode,
        ]);
    }
}
