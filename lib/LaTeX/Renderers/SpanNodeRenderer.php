<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;

final class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    public function escape(string $span): string
    {
        return $span;
    }
}
