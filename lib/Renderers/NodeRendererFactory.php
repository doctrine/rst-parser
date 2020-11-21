<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Nodes\Node;

interface NodeRendererFactory
{
    public function create(Node $node): NodeRenderer;
}
