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
        self::assertTrue(file_exists($this->targetFile('wildcards/bugfix1.html')));
        self::assertTrue(file_exists($this->targetFile('wildcards/feature1.html')));
        self::assertTrue(file_exists($this->targetFile('wildcards/feature2.html')));
        self::assertTrue(file_exists($this->targetFile('wildcards/index.html')));
    }

    public function testMaxDepth(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        // todo::
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderToctree';
    }
}
