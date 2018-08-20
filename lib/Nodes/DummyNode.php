<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

class DummyNode extends Node
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function render() : string
    {
        return '';
    }
}
