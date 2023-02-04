<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

interface SpanRenderer
{
    public function escape(string $span): string;
}
