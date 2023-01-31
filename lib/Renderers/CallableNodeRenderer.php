<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Nodes\CallableNode;

final class CallableNodeRenderer implements NodeRenderer
{
    private CallableNode $callableNode;

    public function __construct(CallableNode $callableNode)
    {
        $this->callableNode = $callableNode;
    }

    public function render(): string
    {
        return $this->callableNode->getCallable()();
    }
}
