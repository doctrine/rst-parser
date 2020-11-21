<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

interface NodeRenderer
{
    public function render(): string;
}
