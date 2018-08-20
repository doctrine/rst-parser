<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

class WrapperNode extends Node
{
    protected $node;
    protected $before;
    protected $after;

    public function __construct($node, $before = '', $after = '')
    {
        $this->node   = $node;
        $this->before = $before;
        $this->after  = $after;
    }

    public function render() : string
    {
        $contents = $this->node ? $this->node->render() : '';

        return $this->before . $contents . $this->after;
    }
}
