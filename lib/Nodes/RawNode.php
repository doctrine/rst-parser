<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

class RawNode extends Node
{
    protected function doRender() : string
    {
        return (string) $this->value;
    }
}
