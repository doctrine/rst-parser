<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderReferenceDoesNotExist;

use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderReferenceDoesNotExistTest extends BaseBuilderTest
{
    protected function configureExpectedErrors(): void
    {
        $this->errorManager->expects(self::atLeastOnce())
            ->method('error')
            ->with('Found invalid reference "does-not-exist"', 'subdir/index', null, null);
    }

    public function testReferenceDoesNotExist(): void
    {
        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertStringContainsString('<p>Test link 1 to</p>', $contents);
        self::assertStringContainsString('<p>Test link 2 to</p>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderReferenceDoesNotExist';
    }
}
