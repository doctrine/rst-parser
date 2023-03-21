<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderUrl;

use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderUrlTest extends BaseBuilderTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration->setUseCachedMetas(false);
    }

    public function testReferenceToSameFile(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));
        self::assertStringContainsString(
            '<a href="index.html">Test reference url</a>',
            $contents
        );
    }

    public function testReferenceToSuDirectoryFile(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));
        self::assertStringContainsString(
            '<a href="subdir/file.html">Subdir file</a>',
            $contents
        );
    }

    public function testReferenceToSameDirectory(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));
        self::assertStringContainsString(
            '<a href="file.html">Test subdir file reference path</a>',
            $contents
        );
    }

    public function testReferenceToParentDirectory(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));
        self::assertStringContainsString(
            '<a href="../index.html">Test subdir reference url</a>',
            $contents
        );
    }

    public function testToctreeToSubDirectory(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));
        self::assertStringContainsString(
            '<li id="subdir-file-html" class="toc-item"><a href="subdir/file.html">Subdirectory File</a></li>',
            $contents
        );
    }

    public function testToctreeToParentDirectory(): void
    {
        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));
        self::assertStringContainsString(
            '<li id="subdir2-file-html" class="toc-item"><a href="../subdir2/file.html">Subdirectory 2 File</a></li>',
            $contents
        );
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderUrl';
    }
}
