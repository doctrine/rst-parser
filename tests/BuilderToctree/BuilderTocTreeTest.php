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

    public function testMaxDepth1(): void
    {
        $contents = $this->getFileContents($this->targetFile('testMaxDepth1.html'));

        self::assertStringContainsString('Level1 - 1', $contents);
        self::assertStringNotContainsString('Level2 - 1', $contents);
        self::assertStringNotContainsString('Level3 - 1', $contents);
        self::assertStringNotContainsString('Level4 - 1', $contents);
        self::assertStringNotContainsString('Level4 - 2', $contents);
        self::assertStringNotContainsString('Level3 - 2', $contents);
        self::assertStringNotContainsString('Level2 - 2', $contents);
        self::assertStringContainsString('Level1 - 2', $contents);
    }

    public function testMaxDepth2(): void
    {
        $contents = $this->getFileContents($this->targetFile('testMaxDepth2.html'));

        self::assertStringContainsString('Level1 - 1', $contents);
        self::assertStringContainsString('Level2 - 1', $contents);
        self::assertStringNotContainsString('Level3 - 1', $contents);
        self::assertStringNotContainsString('Level4 - 1', $contents);
        self::assertStringNotContainsString('Level4 - 2', $contents);
        self::assertStringNotContainsString('Level3 - 2', $contents);
        self::assertStringContainsString('Level2 - 2', $contents);
        self::assertStringContainsString('Level1 - 2', $contents);
    }

    public function testMaxDepth3(): void
    {
        $contents = $this->getFileContents($this->targetFile('testMaxDepth3.html'));

        self::assertStringContainsString('Level1 - 1', $contents);
        self::assertStringContainsString('Level2 - 1', $contents);
        self::assertStringContainsString('Level3 - 1', $contents);
        self::assertStringNotContainsString('Level4 - 1', $contents);
        self::assertStringNotContainsString('Level4 - 2', $contents);
        self::assertStringContainsString('Level3 - 2', $contents);
        self::assertStringContainsString('Level2 - 2', $contents);
        self::assertStringContainsString('Level1 - 2', $contents);
    }

    public function testNoMaxDepth(): void
    {
        $contents = $this->getFileContents($this->targetFile('testNoMaxDepth.html'));

        self::assertStringContainsString('Level1 - 1', $contents);
        self::assertStringContainsString('Level2 - 1', $contents);
        self::assertStringContainsString('Level3 - 1', $contents);
        self::assertStringContainsString('Level4 - 1', $contents);
        self::assertStringContainsString('Level4 - 2', $contents);
        self::assertStringContainsString('Level3 - 2', $contents);
        self::assertStringContainsString('Level2 - 2', $contents);
        self::assertStringContainsString('Level1 - 2', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderToctree';
    }
}
