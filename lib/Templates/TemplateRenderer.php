<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

use Twig\Environment as TwigEnvironment;

interface TemplateRenderer
{
    /**
     * @param mixed[] $parameters
     */
    public function render(string $template, array $parameters = []) : string;

    public function getTwigEnvironment() : TwigEnvironment;
}
