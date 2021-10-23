<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Nodes\Node;

final class CallableNodeRendererFactory implements NodeRendererFactory
{
    /** @var callable */
    private $callable;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public function create(Node $node): NodeRenderer
    {
        return ($this->callable)($node);
    }
}
