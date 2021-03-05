<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderCustomScannerFinder;

use Doctrine\RST\Builder;
use Doctrine\Tests\RST\BaseBuilderTest;
use Symfony\Component\Finder\Finder;

/**
 * Tests a custom Finder for Scanner
 */
class BuilderCustomScannerFinderTest extends BaseBuilderTest
{
    public function testCustomScannerFinder(): void
    {
        self::assertFileExists($this->targetFile('path1/file1.html'));
        self::assertFileDoesNotExist($this->targetFile('path2/file2.html'));
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderCustomScannerFinder';
    }

    protected function configureBuilder(Builder $builder): void
    {
        $finder = new Finder();
        $finder->exclude('path2');

        $builder->setScannerFinder($finder);
    }
}
