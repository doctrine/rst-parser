<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\MetaNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class MetaNodeRenderer implements NodeRenderer
{
    private MetaNode $metaNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(MetaNode $metaNode, TemplateRenderer $templateRenderer)
    {
        $this->metaNode         = $metaNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        return $this->templateRenderer->render('meta.html.twig', [
            'metaNode' => $this->metaNode,
        ]);
    }
}
