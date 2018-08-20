<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

class RawNode extends Node
{
    public function render() : string
    {
        return $this->value;
    }
}
