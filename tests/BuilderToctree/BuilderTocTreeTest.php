<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderToctree;

use Doctrine\Tests\RST\BaseBuilderTest;

use function file_exists;

class BuilderTocTreeTest extends BaseBuilderTest
{
    public function testTocTreeGlob(): void
    {
        self::assertTrue(file_exists($this->targetFile('level1-1/index.html')));
        self::assertTrue(file_exists($this->targetFile('level1-2/index.html')));
        self::assertTrue(file_exists($this->targetFile('level1-1/level2-1/index.html')));
        self::assertTrue(file_exists($this->targetFile('level1-1/level2-2/index.html')));
        self::assertTrue(file_exists($this->targetFile('level1-1/level2-1/level3-1/index.html')));
        self::assertTrue(file_exists($this->targetFile('level1-1/level2-1/level3-2/index.html')));
        self::assertTrue(file_exists($this->targetFile('level1-1/level2-1/level3-1/level4-1/index.html')));
        self::assertTrue(file_exists($this->targetFile('level1-1/level2-1/level3-1/level4-2/index.html')));
        self::assertTrue(file_exists($this->targetFile('subdir/toctree.html')));
        self::assertTrue(file_exists($this->targetFile('orphaned/file.html')));
        self::assertTrue(file_exists($this->targetFile('wildcards/bugfix1.html')));
        self::assertTrue(file_exists($this->targetFile('wildcards/feature1.html')));
        self::assertTrue(file_exists($this->targetFile('wildcards/feature2.html')));
        self::assertTrue(file_exists($this->targetFile('wildcards/index.html')));
    }

    public function testMaxDepth(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        // :maxdepth: 1
        self::assertStringContainsString('<ul><li id="level1-1-index-html" class="toc-item"><a href="level1-1/index.html">Level1 - 1</a></li><li id="level1-2-index-html" class="toc-item"><a href="level1-2/index.html">Level1 - 2</a></li></ul>', $contents);

        // :maxdepth: 2
        self::assertStringContainsString('<ul><li id="level1-1-index-html" class="toc-item"><a href="level1-1/index.html">Level1 - 1</a><ul><li id="level2-1-index-html" class="toc-item"><a href="level2-1/index.html">Level2 - 1</a></li><li id="level2-2-index-html" class="toc-item"><a href="level2-2/index.html">Level2 - 2</a></li></ul></li></ul>', $contents);

        // :maxdepth: 3
        self::assertStringContainsString('<ul><li id="level1-1-index-html" class="toc-item"><a href="level1-1/index.html">Level1 - 1</a><ul><li id="level2-1-index-html" class="toc-item"><a href="level2-1/index.html">Level2 - 1</a><ul><li id="level3-1-index-html" class="toc-item"><a href="level3-1/index.html">Level3 - 1</a></li><li id="level3-2-index-html" class="toc-item"><a href="level3-2/index.html">Level3 - 2</a></li></ul></li><li id="level2-2-index-html" class="toc-item"><a href="level2-2/index.html">Level2 - 2</a></li></ul></li><li id="level1-2-index-html" class="toc-item"><a href="level1-2/index.html">Level1 - 2</a></li></ul>', $contents);

        // :maxdepth: 4
        self::assertStringContainsString('<ul><li id="level1-1-index-html" class="toc-item"><a href="level1-1/index.html">Level1 - 1</a><ul><li id="level2-1-index-html" class="toc-item"><a href="level2-1/index.html">Level2 - 1</a><ul><li id="level3-1-index-html" class="toc-item"><a href="level3-1/index.html">Level3 - 1</a><ul><li id="level4-1-index-html" class="toc-item"><a href="level4-1/index.html">Level4 - 1</a></li><li id="level4-2-index-html" class="toc-item"><a href="level4-2/index.html">Level4 - 2</a></li></ul></li><li id="level3-2-index-html" class="toc-item"><a href="level3-2/index.html">Level3 - 2</a></li></ul></li><li id="level2-2-index-html" class="toc-item"><a href="level2-2/index.html">Level2 - 2</a></li></ul></li><li id="level1-2-index-html" class="toc-item"><a href="level1-2/index.html">Level1 - 2</a></li></ul>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderToctree';
    }
}
