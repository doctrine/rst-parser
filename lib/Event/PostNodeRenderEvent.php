<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Renderers\RenderedNode;

final class PostNodeRenderEvent extends EventArgs
{
    public const POST_NODE_RENDER = 'postNodeRender';

    /** @var RenderedNode */
    private $renderedNode;

    public function __construct(RenderedNode $renderedNode)
    {
        $this->renderedNode = $renderedNode;
    }

    public function getRenderedNode() : RenderedNode
    {
        return $this->renderedNode;
    }
}
