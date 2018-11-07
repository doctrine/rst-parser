<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\ImageNode as Base;

class ImageNode extends Base
{
    protected function doRender() : string
    {
        $attributes = [];
        foreach ($this->options as $key => $value) {
            $attributes[] = $key . '=' . $value;
        }

        return '\includegraphics{' . $this->url . '}';
    }
}
