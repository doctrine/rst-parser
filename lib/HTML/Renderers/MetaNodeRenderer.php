<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Nodes\MetaNode;
use Doctrine\RST\Renderers\NodeRenderer;
use function htmlspecialchars;

class MetaNodeRenderer implements NodeRenderer
{
    /** @var MetaNode */
    private $metaNode;

    public function __construct(MetaNode $metaNode)
    {
        $this->metaNode = $metaNode;
    }

    public function render() : string
    {
        return '<meta name="' . htmlspecialchars($this->metaNode->getKey()) . '" content="' . htmlspecialchars($this->metaNode->getValue()) . '" />';
    }
}
