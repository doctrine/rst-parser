<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

class WrapperNode extends Node
{
    /** @var Node|null */
    private $node;

    /** @var string */
    private $before;

    /** @var string */
    private $after;

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
