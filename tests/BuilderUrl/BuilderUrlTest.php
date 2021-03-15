<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderUrl;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use Doctrine\Tests\RST\BaseBuilderTest;

use function strpos;

class BuilderUrlTest extends BaseBuilderTest
{
    /** @var Configuration */
    private $configuration;

    public function testBaseUrl(): void
    {
        $this->configuration->setBaseUrl('https://www.domain.com/directory');

        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(
            '<a href="https://www.domain.com/directory/index.html">Test reference url</a>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="index-html-base-url" class="toc-item"><a href="https://www.domain.com/directory/index.html#base-url">Base URL</a></li>',
            $contents
        );

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertStringContainsString(
            '<a href="https://www.domain.com/directory/index.html">Test subdir reference url</a>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="index-html" class="toc-item"><a href="https://www.domain.com/directory/index.html">Base URL</a></li>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="file-html" class="toc-item"><a href="https://www.domain.com/directory/subdir/file.html">Subdirectory File</a></li>',
            $contents
        );
    }

    public function testBaseUrlEnabledCallable(): void
    {
        $this->configuration->setBaseUrl('https://www.domain.com/directory');
        $this->configuration->setBaseUrlEnabledCallable(static function (string $path): bool {
            return strpos($path, 'subdir/') !== 0;
        });

        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(
            '<a href="https://www.domain.com/directory/index.html">Test reference url</a>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="index-html-base-url" class="toc-item"><a href="https://www.domain.com/directory/index.html#base-url">Base URL</a></li>',
            $contents
        );

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertStringContainsString(
            '<a href="https://www.domain.com/directory/index.html">Test subdir reference url</a>',
            $contents
        );

        self::assertStringContainsString(
            '<a href="file.html">Test subdir file reference path</a>',
            $contents
        );

        self::assertStringContainsString(
            '<a href="index.html#subdirectory-index">Subdirectory Index</a>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="index-html" class="toc-item"><a href="https://www.domain.com/directory/index.html">Base URL</a></li>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="file-html" class="toc-item"><a href="file.html">Subdirectory File</a></li>',
            $contents
        );
    }

    public function testRelativeUrl(): void
    {
        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(
            '<a href="index.html">Test reference url</a>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="index-html-base-url" class="toc-item"><a href="index.html#base-url">Base URL</a></li>',
            $contents
        );

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertStringContainsString(
            '<a href="../index.html">Test subdir reference url</a>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="index-html" class="toc-item"><a href="../index.html">Base URL</a></li>',
            $contents
        );

        self::assertStringContainsString(
            '<li id="file-html" class="toc-item"><a href="file.html">Subdirectory File</a></li>',
            $contents
        );
    }

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setUseCachedMetas(false);
        $this->builder = new Builder(new Kernel($this->configuration));
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderUrl';
    }
}
