<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

class WrapperNode extends Node
{
    /** @var null|Node */
    protected $node;

    /** @var string */
    protected $before;

    /** @var string */
    protected $after;

    public function __construct(?Node $node, string $before = '', string $after = '')
    {
        parent::__construct();

        $this->node   = $node;
        $this->before = $before;
        $this->after  = $after;
    }

    public function render() : string
    {
        $contents = $this->node !== null ? $this->node->render() : '';

        return $this->before . $contents . $this->after;
    }
}
