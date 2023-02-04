<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\References\ResolvedReference;

interface SpanRenderer
{
    public function nbsp(): string;

    /** @param mixed[] $attributes */
    public function link(?string $url, string $title, array $attributes = []): string;

    public function escape(string $span): string;

    /** @param string[] $value */
    public function reference(ResolvedReference $reference, array $value): string;
}
