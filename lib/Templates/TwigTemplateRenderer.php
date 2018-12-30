<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

use Doctrine\RST\Configuration;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;
use function rtrim;
use function sprintf;

class TwigTemplateRenderer implements TemplateRenderer
{
    /** @var Configuration */
    private $configuration;
    /** @var TwigEnvironment */
    private $twigEnvironment;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param mixed[] $parameters
     */
    public function render(string $template, array $parameters = []) : string
    {
        return rtrim($this->getTwigEnvironment()->render($template, $parameters), "\n");
    }

    private function getThemeDir(string $templatesDir, ?string $theme = null) : string
    {
        $theme         = $theme ?? $this->configuration->getTheme();
        $fileExtension = $this->configuration->getFileExtension();

        return $templatesDir . '/' . $theme . '/' . $fileExtension;
    }

    /**
     * @return string[]
     */
    private function getTemplatesDirs() : array
    {
        $themeDirs = [];

        // check custom template directories first
        $customTemplateDirs = $this->configuration->getCustomTemplateDirs();

        if ($customTemplateDirs !== []) {
            foreach ($customTemplateDirs as $customTemplateDir) {
                $themeDirs[] = $this->getThemeDir($customTemplateDir);
            }
        }

        // fallback to core templates for the configured theme
        $themeDirs[] = $this->getThemeDir(__DIR__);

        // fallback to core templates for the default theme
        // if the configured theme is not the default
        if ($this->configuration->getTheme() !== Configuration::THEME_DEFAULT) {
            $themeDirs[] = $this->getThemeDir(__DIR__, Configuration::THEME_DEFAULT);
        }

        return $themeDirs;
    }

    public function getTwigEnvironment() : TwigEnvironment
    {
        if (null !== $this->twigEnvironment) {
            return $this->twigEnvironment;
        }

        $loader = new FilesystemLoader($this->getTemplatesDirs());

        return $this->twigEnvironment = new TwigEnvironment($loader, [
            'strict_variables' => true,
            'cache' => sprintf('%s/twig', $this->configuration->getCacheDir()),
            'auto_reload' => true,
        ]);
    }
}
