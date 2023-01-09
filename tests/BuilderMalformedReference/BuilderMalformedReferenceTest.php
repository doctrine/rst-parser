<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderMalformedReference;

use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderMalformedReferenceTest extends BaseBuilderTest
{
    protected function configureExpectedErrors(): void
    {
        $this->errorManager->expects(self::atLeastOnce())
            ->method('error')
            ->with('Found invalid reference "_test_reference"', 'subdir/another', null, null);
    }

    public function testMalformedReferenceLogsError(): void
    {
        $this->configureExpectedErrors();
        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    public function testMalformedReferenceWithIgnoredInvalidReferences(): void
    {
        $this->configuration->setIgnoreInvalidReferences(true);
        // test that invalid references can be ignored and no exception gets thrown
        $this->errorManager->expects(self::never())->method('error');
        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('subdir/another.html'));

        self::assertStringContainsString('<p>Test link to</p>', $contents);

        $contents = $this->getFileContents($this->targetFile('subdir/index.html'));

        self::assertStringContainsString('<a id="test_reference"></a>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderMalformedReference';
    }
}
