<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

use Doctrine\RST\Configuration;
use Twig\Environment as TwigEnvironment;

final class TwigAdapter implements TemplateEngineAdapter
{
    /** @var Configuration */
    private $configuration;

    /** @var TwigEnvironment */
    private $twigEnvironment;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getTemplateEngine(): TwigEnvironment
    {
        if ($this->twigEnvironment === null) {
            $this->twigEnvironment = TwigEnvironmentFactory::createTwigEnvironment($this->configuration);
        }

        return $this->twigEnvironment;
    }

    /**
     * @param mixed[] $parameters
     */
    public function render(string $template, array $parameters = []): string
    {
        return $this->getTemplateEngine()->render($template, $parameters);
    }
}
