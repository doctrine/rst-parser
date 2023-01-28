<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Environment;

interface RendererFactory
{
    public function create(Environment $environment): Renderer;
}
