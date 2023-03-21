<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderUrl;

use Doctrine\RST\Builder;
use Doctrine\Tests\RST\BaseBuilderTest;

use function strpos;

class BuilderUrlWithBaseUrlCallableTest extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder): void
    {
        $this->configuration->setBaseUrl('https://www.domain.com/directory');
        $this->configuration->setBaseUrlEnabledCallable(static fn (string $path): bool => strpos($path, 'subdir/') !== 0);
        $this->configuration->setUseCachedMetas(false);
    }

    public function testReferenceHasBaseUrl(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(
            '<a href="https://www.domain.com/directory/index.html">Test reference url</a>',
            $contents
        );
    }

    public function testReferenceToSubDirectoryHasNoBaseUrl(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertStringContainsString(
            '<a href="file.html">Test subdir file reference path</a>',
            $contents
        );
    }

    public function testTocTreeHasBaseUrl(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertStringContainsString(
            '<a href="https://www.domain.com/directory/subdir2/file.html">Subdirectory 2 File</a>',
            $contents
        );
    }

    public function testTocTreeToSubDirHasNoBaseUrl(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(
            '<a href="subdir/index.html">Subdirectory Index</a>',
            $contents
        );
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderUrl';
    }
}
