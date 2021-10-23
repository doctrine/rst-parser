<?php

declare(strict_types=1);

namespace Doctrine\RST\Templates;

use Doctrine\RST\Configuration;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

use function file_exists;
use function sprintf;

final class TwigEnvironmentFactory
{
    public static function createTwigEnvironment(Configuration $configuration): TwigEnvironment
    {
        $loader = new FilesystemLoader(self::getTemplateDirs($configuration));

        return new TwigEnvironment($loader, [
            'strict_variables' => true,
            'cache' => sprintf('%s/twig', $configuration->getCacheDir()),
            'auto_reload' => true,
        ]);
    }

    /**
     * @return string[]
     */
    private static function getTemplateDirs(Configuration $configuration): array
    {
        $themeDirs     = [];
        $fileExtension = $configuration->getFileExtension();

        $templateDirectories = $configuration->getCustomTemplateDirs();
        // add the fallback directory
        $templateDirectories[] = __DIR__;

        foreach ($templateDirectories as $templateDir) {
            $themePath = $templateDir . '/' . $configuration->getTheme() . '/' . $fileExtension;
            if (! file_exists($themePath)) {
                continue;
            }

            $themeDirs[] = $themePath;
        }

        // look for the fallback "default" in all directories
        if ($configuration->getTheme() !== Configuration::THEME_DEFAULT) {
            foreach ($templateDirectories as $templateDir) {
                $themePath = $templateDir . '/' . Configuration::THEME_DEFAULT . '/' . $fileExtension;
                if (! file_exists($themePath)) {
                    continue;
                }

                $themeDirs[] = $themePath;
            }
        }

        return $themeDirs;
    }
}
