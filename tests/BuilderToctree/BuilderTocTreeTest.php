<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderToctree;

use Doctrine\Tests\RST\BaseBuilderTest;

use function file_exists;

class BuilderTocTreeTest extends BaseBuilderTest
{
    public function testTocTreeGlob(): void
    {
        self::assertTrue(file_exists($this->targetFile('subdir/toctree.html')));
        self::assertTrue(file_exists($this->targetFile('orphaned/file.html')));
    }

    public function testMaxDepth(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        // :maxdepth: 1
        self::assertStringContainsString('<div class="toc"><ul><li id="index-html-title" class="toc-item"><a href="index.html#title">Title</a></li></ul></div>', $contents);

        // :maxdepth: 2
        self::assertStringContainsString('<div class="toc"><ul><li id="index-html-title" class="toc-item"><a href="index.html#title">Title</a><ul><li id="index-html-max-depth-level-2" class="toc-item"><a href="index.html#max-depth-level-2">Max Depth Level 2</a></li></ul></li></ul></div>', $contents);

        // :maxdepth: 3
        self::assertStringContainsString('<div class="toc"><ul><li id="index-html-title" class="toc-item"><a href="index.html#title">Title</a><ul><li id="index-html-max-depth-level-2" class="toc-item"><a href="index.html#max-depth-level-2">Max Depth Level 2</a><ul><li id="index-html-max-depth-level-3" class="toc-item"><a href="index.html#max-depth-level-3">Max Depth Level 3</a></li></ul></li></ul></li></ul></div>', $contents);

        // :maxdepth: 4
        self::assertStringContainsString('<div class="toc"><ul><li id="index-html-title" class="toc-item"><a href="index.html#title">Title</a><ul><li id="index-html-max-depth-level-2" class="toc-item"><a href="index.html#max-depth-level-2">Max Depth Level 2</a><ul><li id="index-html-max-depth-level-3" class="toc-item"><a href="index.html#max-depth-level-3">Max Depth Level 3</a><ul><li id="index-html-max-depth-level-4" class="toc-item"><a href="index.html#max-depth-level-4">Max Depth Level 4</a></li></ul></li></ul></li></ul></li></ul></div>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderToctree';
    }
}
