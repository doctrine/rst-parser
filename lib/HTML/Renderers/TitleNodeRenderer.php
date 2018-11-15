<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
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
        $value = $this->titleNode->getValue()->render();
        $level = $this->titleNode->getLevel();

        $id = Environment::slugify($value);

        return $this->templateRenderer->render('header-title.html.twig', [
            'titleNode' => $this->titleNode,
            'id' => $id,
            'level' => $level,
            'value' => $value,
        ]);
    }
}
