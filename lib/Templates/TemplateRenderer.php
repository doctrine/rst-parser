<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

interface TemplateRenderer
{
    /** @param mixed[] $parameters */
    public function render(string $template, array $parameters = []): string;
}
