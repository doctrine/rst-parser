<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class SeparatorNodeRenderer implements NodeRenderer
{
    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(TemplateRenderer $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return $this->templateRenderer->render('separator.html.twig');
    }
}
