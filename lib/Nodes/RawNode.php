<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

class RawNode extends Node
{
    public function render() : string
    {
        return (string) $this->value;
    }
}
