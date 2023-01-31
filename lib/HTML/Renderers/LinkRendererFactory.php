<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Renderers\LinkRendererFactory as AbstractLinkRendererFactory;

class LinkRendererFactory extends AbstractLinkRendererFactory
{
    public function create(Environment $environment): LinkRenderer
    {
        return new LinkRenderer($environment);
    }
}
