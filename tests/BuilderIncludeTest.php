<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Builder;
use PHPUnit\Framework\TestCase;
use function file_exists;
use function file_get_contents;
use function shell_exec;

/**
 * Unit testing build with ".. include::" directive
 */
class BuilderIncludeTest extends TestCase
{
    public function testTocTreeGlob() : void
    {
        shell_exec('rm -rf ' . $this->targetFile());
        $builder = new Builder();
        $builder->setUseRelativeUrls(true);
        $builder->build($this->sourceFile(), $this->targetFile(), false);

        self::assertTrue(file_exists($this->targetFile('index.html')));
        self::assertContains('This file is included', file_get_contents($this->targetFile('index.html')));

        foreach ($builder->getDocuments() as $document) {
            foreach ($document->getEnvironment()->getMetas()->getAll() as $meta) {
                foreach ($meta->getTocs() as $toc) {
                    self::assertNotContains('include.inc', $toc);
                }
            }
        }
    }

    private function sourceFile(string $file = '') : string
    {
        return __DIR__ . '/builder-include/input/' . $file;
    }

    private function targetFile(string $file = '') : string
    {
        return __DIR__ . '/builder-include/output/' . $file;
    }
}
