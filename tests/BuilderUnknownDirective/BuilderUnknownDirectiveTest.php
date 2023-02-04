<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderUnknownDirective;

use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderUnknownDirectiveTest extends BaseBuilderTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration->abortOnError(false);
    }

    public function testUnknownDirectiveWithFieldOptions(): void
    {
        $this->builder->build($this->sourceFile(), $this->targetFile());
        $contents = $this->getFileContents($this->targetFile('index.html'));
        self::assertStringNotContainsString('<p>Test link 2 to</p>', $contents);
    }

    protected function configureExpectedErrors(): void
    {
        $this->errorManager->expects(self::atLeastOnce())
            ->method('error')
            ->with('Unknown directive "unknown-directive" for line ".. unknown-directive:: some content"');
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderUnknownDirective';
    }
}
