<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

final class TitleNodeRenderer implements NodeRenderer
{
    private TitleNode $titleNode;

    private TemplateRenderer $templateRenderer;

    public function __construct(TitleNode $titleNode, TemplateRenderer $templateRenderer)
    {
        $this->titleNode        = $titleNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $type = 'chapter';

        if ($this->titleNode->getLevel() > 1) {
            $type = 'section';

            for ($i = 2; $i < $this->titleNode->getLevel(); $i++) {
                $type = 'sub' . $type;
            }
        }

        return $this->templateRenderer->render('title.tex.twig', [
            'type' => $type,
            'titleNode' => $this->titleNode,
        ]);
    }
}
