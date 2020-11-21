<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Nodes\Node;

class DefaultNodeRenderer implements NodeRenderer
{
    /** @var Node */
    private $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function render(): string
    {
        $value = $this->node->getValue();

        if ($value instanceof Node) {
            return $value->render();
        }

        if ($value === null) {
            return '';
        }

        return $value;
    }
}
