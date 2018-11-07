<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\ImageNode as Base;
use function htmlspecialchars;

class ImageNode extends Base
{
    protected function doRender() : string
    {
        $attributes = '';
        foreach ($this->options as $key => $value) {
            $attributes .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        return '<img src="' . $this->url . '" ' . $attributes . ' />';
    }
}
