<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

final class WrapperNode extends Node
{
    private ?Node $node = null;

    private string $before;

    private string $after;

    public function __construct(?Node $node, string $before = '', string $after = '')
    {
        parent::__construct();

        $this->node   = $node;
        $this->before = $before;
        $this->after  = $after;
    }

    protected function doRender(): string
    {
        $contents = $this->node !== null ? $this->node->render() : '';

        return $this->before . $contents . $this->after;
    }
}
