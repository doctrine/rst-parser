<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\AnchorNode;
use Doctrine\RST\Renderers\NodeRenderer;

class AnchorNodeRenderer implements NodeRenderer
{
    /** @var AnchorNode */
    private $anchorNode;

    public function __construct(AnchorNode $anchorNode)
    {
        $this->anchorNode = $anchorNode;
    }

    public function render() : string
    {
        return '\label{' . $this->anchorNode->getValue() . '}';
    }
}
