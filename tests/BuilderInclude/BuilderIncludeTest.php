<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderInclude;

use Doctrine\Tests\RST\BaseBuilderTest;

use function file_exists;
use function file_get_contents;

/**
 * Unit testing build with ".. include::" directive
 */
class BuilderIncludeTest extends BaseBuilderTest
{
    public function testTocTreeGlob(): void
    {
        self::assertTrue(file_exists($this->targetFile('index.html')));
        self::assertContains('This file is included', file_get_contents($this->targetFile('index.html')));

        foreach ($this->builder->getDocuments()->getAll() as $document) {
            foreach ($document->getEnvironment()->getMetas()->getAll() as $meta) {
                foreach ($meta->getTocs() as $toc) {
                    self::assertNotContains('include.inc', $toc);
                }
            }
        }
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderInclude';
    }
}
