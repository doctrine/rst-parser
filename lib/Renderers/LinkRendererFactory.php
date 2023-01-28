<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Environment;

abstract class LinkRendererFactory implements RendererFactory
{
    abstract public function create(Environment $environment): LinkRenderer;
}
