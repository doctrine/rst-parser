<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderInvalidReferences;

use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderInvalidReferencesTest extends BaseBuilderTest
{
    public function testInvalidReference(): void
    {
        $this->configureExpectedErrors();
        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    protected function configureExpectedErrors(): void
    {
        $this->errorManager->expects(self::atLeastOnce())
            ->method('error')
            ->with('Found invalid reference "does_not_exist"', 'index', null, null);
    }

    public function testInvalidReferenceIgnored(): void
    {
        $this->configuration->setIgnoreInvalidReferences(true);

        $this->errorManager->expects(self::never())->method('error');

        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString('<p>Test unresolved reference</p>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderInvalidReferences';
    }
}
