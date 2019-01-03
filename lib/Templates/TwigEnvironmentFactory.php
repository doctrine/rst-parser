<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

use Doctrine\RST\Configuration;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;
use function sprintf;

class TwigEnvironmentFactory
{
    public static function createTwigEnvironment(Configuration $configuration) : TwigEnvironment
    {
        $loader = new FilesystemLoader(self::getTemplatesDirs($configuration));

        return new TwigEnvironment($loader, [
            'strict_variables' => true,
            'cache' => sprintf('%s/twig', $configuration->getCacheDir()),
            'auto_reload' => true,
        ]);
    }

    private static function getThemeDir(Configuration $configuration, string $templatesDir, ?string $theme = null) : string
    {
        $theme         = $theme ?? $configuration->getTheme();
        $fileExtension = $configuration->getFileExtension();

        return $templatesDir . '/' . $theme . '/' . $fileExtension;
    }

    /**
     * @return string[]
     */
    private static function getTemplatesDirs(Configuration $configuration) : array
    {
        $themeDirs = [];

        // check custom template directories first
        $customTemplateDirs = $configuration->getCustomTemplateDirs();

        if ($customTemplateDirs !== []) {
            foreach ($customTemplateDirs as $customTemplateDir) {
                $themeDirs[] = self::getThemeDir($configuration, $customTemplateDir);
            }
        }

        // fallback to core templates for the configured theme
        $themeDirs[] = self::getThemeDir($configuration, __DIR__);

        // fallback to core templates for the default theme
        // if the configured theme is not the default
        if ($configuration->getTheme() !== Configuration::THEME_DEFAULT) {
            $themeDirs[] = self::getThemeDir($configuration, __DIR__, Configuration::THEME_DEFAULT);
        }

        return $themeDirs;
    }
}
