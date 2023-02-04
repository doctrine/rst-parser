<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Renderers\SpanNodeRenderer as BaseSpanNodeRenderer;

use function htmlspecialchars;

use const ENT_COMPAT;

final class SpanNodeRenderer extends BaseSpanNodeRenderer
{
    public function escape(string $span): string
    {
        return htmlspecialchars($span, ENT_COMPAT);
    }
}
