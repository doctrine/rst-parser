<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Builder;
use PHPUnit\Framework\TestCase;
use function file_exists;
use function shell_exec;

/**
 * Unit testing for toc tree with :glob: option
 */
class BuilderTocTreeGlobTest extends TestCase
{
    public function setUp() : void
    {
        shell_exec('rm -rf ' . $this->targetFile());
        $builder = new Builder();
        $builder->setUseRelativeUrls(true);
        $builder->build($this->sourceFile(), $this->targetFile());
    }

    public function testTocTreeGlob() : void
    {
        self::assertTrue(file_exists($this->targetFile('subdir/toctree.html')));
        self::assertFalse(file_exists($this->targetFile('not-parsed/file.html')));
    }

    private function sourceFile(string $file = '') : string
    {
        return __DIR__ . '/builder-toctree-glob/input/' . $file;
    }

    private function targetFile(string $file = '') : string
    {
        return __DIR__ . '/builder-toctree-glob/output/' . $file;
    }
}
