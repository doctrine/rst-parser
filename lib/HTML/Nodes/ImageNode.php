<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\ImageNode as Base;
use function htmlspecialchars;

class ImageNode extends Base
{
    public function render() : string
    {
        $attributes = '';
        foreach ($this->options as $key => $value) {
            $attributes .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        return '<img src="' . $this->url . '" ' . $attributes . ' />';
    }
}
