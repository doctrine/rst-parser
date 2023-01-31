<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

use Doctrine\RST\Configuration;

use function rtrim;

final class TwigTemplateRenderer implements TemplateRenderer
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /** @param mixed[] $parameters */
    public function render(string $template, array $parameters = []): string
    {
        return rtrim($this->configuration->getTemplateEngine()->render($template, $parameters), "\n");
    }
}
