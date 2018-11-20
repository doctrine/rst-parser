<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class TitleNodeRenderer implements NodeRenderer
{
    /** @var TitleNode */
    private $titleNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(TitleNode $titleNode, TemplateRenderer $templateRenderer)
    {
        $this->titleNode        = $titleNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return $this->templateRenderer->render('header-title.html.twig', [
            'titleNode' => $this->titleNode,
        ]);
    }
}
