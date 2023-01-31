<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Environment;

abstract class Renderer
{
    protected Environment $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }
}
