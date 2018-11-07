<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\ImageNode;
use Doctrine\RST\Renderers\NodeRenderer;
use function htmlspecialchars;

class ImageNodeRenderer implements NodeRenderer
{
    /** @var ImageNode */
    private $imageNode;

    public function __construct(ImageNode $imageNode)
    {
        $this->imageNode = $imageNode;
    }

    public function render() : string
    {
        $attributes = '';

        foreach ($this->imageNode->getOptions() as $key => $value) {
            $attributes .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        return '<img src="' . $this->imageNode->getUrl() . '" ' . $attributes . ' />';
    }
}
