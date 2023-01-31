<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Nodes\Node;

final class RenderedNode
{
    private Node $node;

    private string $rendered;

    public function __construct(Node $node, string $rendered)
    {
        $this->node     = $node;
        $this->rendered = $rendered;
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setRendered(string $rendered): void
    {
        $this->rendered = $rendered;
    }

    public function getRendered(): string
    {
        return $this->rendered;
    }
}
