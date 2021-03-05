<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Templates;

use Doctrine\RST\Configuration;
use Doctrine\RST\Templates\TwigEnvironmentFactory;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function realpath;
use function sys_get_temp_dir;

class TwigEnvironmentFactoryTest extends TestCase
{
    /** @var string */
    private $tmpPath;
    /** @var Filesystem */
    private $filesystem;

    public function testTemplateDirectoriesNothingCustom(): void
    {
        $configuration = new Configuration();
        $configuration->setFileExtension('html');

        // no theme, no custom dirs
        self::assertLoaderPaths(
            [(string) realpath(__DIR__ . '/../../lib/Templates/default/html')],
            TwigEnvironmentFactory::createTwigEnvironment($configuration)
        );
    }

    public function testTemplateDirectoriesThemeAndDirectories(): void
    {
        $configuration = new Configuration();
        $configuration->setFileExtension('html');
        $configuration->setTheme('cool_theme');

        $dir1           = $this->tmpPath . '/dir1';
        $dir2           = $this->tmpPath . '/dir2';
        $nonExistentDir = $this->tmpPath . '/dir3';

        // dir1 has all directories
        $this->filesystem->mkdir($dir1);
        $this->filesystem->mkdir($dir1 . '/default/html');
        $this->filesystem->mkdir($dir1 . '/cool_theme/html');

        // dir2 has just the theme
        $this->filesystem->mkdir($dir2);
        $this->filesystem->mkdir($dir2 . '/cool_theme/html');

        $configuration->setCustomTemplateDirs([$dir1, $dir2, $nonExistentDir]);

        // no theme, no custom dirs
        self::assertLoaderPaths(
            [
                $dir1 . '/cool_theme/html',
                $dir2 . '/cool_theme/html',
                $dir1 . '/default/html',
                (string) realpath(__DIR__ . '/../../lib/Templates/default/html'),
            ],
            TwigEnvironmentFactory::createTwigEnvironment($configuration)
        );
    }

    /**
     * @param string[] $expectedPaths
     */
    private static function assertLoaderPaths(array $expectedPaths, Environment $twig): void
    {
        $loader = $twig->getLoader();
        if (! $loader instanceof FilesystemLoader) {
            throw new Exception('Wrong loader instance');
        }

        self::assertSame($expectedPaths, $loader->getPaths());
    }

    protected function setUp(): void
    {
        $this->tmpPath    = sys_get_temp_dir() . '/_rst_twig_tests';
        $this->filesystem = new Filesystem();
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->tmpPath);
    }
}
