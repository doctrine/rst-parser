<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

interface TemplateEngineAdapter
{
    /** @return mixed */
    public function getTemplateEngine();

    /** @param mixed[] $parameters */
    public function render(string $template, array $parameters = []): string;
}
