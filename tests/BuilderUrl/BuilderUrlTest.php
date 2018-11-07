<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderUrl;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\Tests\RST\BaseBuilderTest;
use function strpos;

class BuilderUrlTest extends BaseBuilderTest
{
    /** @var Configuration */
    private $configuration;

    public function testBaseUrl() : void
    {
        $this->configuration->setBaseUrl('https://www.domain.com/directory');

        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains(
            '<a href="https://www.domain.com/directory/index.html">Test reference url</a>',
            $contents
        );

        self::assertContains(
            '<li id="index-html-base-url" class="toc-item"><a href="https://www.domain.com/directory/index.html#base-url">Base URL</a></li>',
            $contents
        );

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertContains(
            '<a href="https://www.domain.com/directory/index.html">Test subdir reference url</a>',
            $contents
        );

        self::assertContains(
            '<li id="index-html-base-url" class="toc-item"><a href="https://www.domain.com/directory/index.html#base-url">Base URL</a></li>',
            $contents
        );

        self::assertContains(
            '<li id="file-html-subdirectory-file" class="toc-item"><a href="https://www.domain.com/directory/subdir/file.html#subdirectory-file">Subdirectory File</a></li>',
            $contents
        );
    }

    public function testBaseUrlEnabledCallable() : void
    {
        $this->configuration->setBaseUrl('https://www.domain.com/directory');
        $this->configuration->setBaseUrlEnabledCallable(static function (string $path) : bool {
            return strpos($path, 'subdir/index') !== false;
        });

        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains(
            '<a href="index.html">Test reference url</a>',
            $contents
        );

        self::assertContains(
            '<li id="index-html-base-url" class="toc-item"><a href="index.html#base-url">Base URL</a></li>',
            $contents
        );

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertContains(
            '<a href="https://www.domain.com/directory/index.html">Test subdir reference url</a>',
            $contents
        );

        self::assertContains(
            '<li id="index-html-base-url" class="toc-item"><a href="https://www.domain.com/directory/index.html#base-url">Base URL</a></li>',
            $contents
        );

        self::assertContains(
            '<li id="file-html-subdirectory-file" class="toc-item"><a href="https://www.domain.com/directory/subdir/file.html#subdirectory-file">Subdirectory File</a></li>',
            $contents
        );
    }

    public function testRelativeUrl() : void
    {
        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains(
            '<a href="index.html">Test reference url</a>',
            $contents
        );

        self::assertContains(
            '<li id="index-html-base-url" class="toc-item"><a href="index.html#base-url">Base URL</a></li>',
            $contents
        );

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertContains(
            '<a href="../index.html">Test subdir reference url</a>',
            $contents
        );

        self::assertContains(
            '<li id="index-html-base-url" class="toc-item"><a href="../index.html#base-url">Base URL</a></li>',
            $contents
        );

        self::assertContains(
            '<li id="file-html-subdirectory-file" class="toc-item"><a href="file.html#subdirectory-file">Subdirectory File</a></li>',
            $contents
        );
    }

    protected function setUp() : void
    {
        $this->configuration = new Configuration();
        $this->builder       = new Builder(null, $this->configuration);
    }

    protected function getFixturesDirectory() : string
    {
        return 'BuilderUrl';
    }
}
