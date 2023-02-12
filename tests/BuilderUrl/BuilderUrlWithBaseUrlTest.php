<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderUrl;

use Doctrine\RST\Builder;
use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderUrlWithBaseUrlTest extends BaseBuilderTest
{
    protected function configureBuilder(Builder $builder): void
    {
        $this->configuration->setBaseUrl('https://www.domain.com/directory');
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

    public function testTocTreeHasBaseUrl(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(
            '<li id="subdir-file-html" class="toc-item"><a href="https://www.domain.com/directory/subdir/file.html">Subdirectory File</a>',
            $contents
        );
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderUrl';
    }
}
