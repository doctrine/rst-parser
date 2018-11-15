<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\ImageNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class ImageNodeRenderer implements NodeRenderer
{
    /** @var ImageNode */
    private $imageNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(ImageNode $imageNode, TemplateRenderer $templateRenderer)
    {
        $this->imageNode        = $imageNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render() : string
    {
        return $this->templateRenderer->render('image.html.twig', [
            'imageNode' => $this->imageNode,
        ]);
    }
}
