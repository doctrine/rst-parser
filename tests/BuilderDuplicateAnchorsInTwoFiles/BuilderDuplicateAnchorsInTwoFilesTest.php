<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderDuplicateAnchorsInTwoFiles;

use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderDuplicateAnchorsInTwoFilesTest extends BaseBuilderTest
{
    public function testDuplicateReferenceSetsWarning(): void
    {
        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    public function testInvalidReferenceRenamed(): void
    {
        $this->configuration->setIgnoreInvalidReferences(true);

        $this->builder->build($this->sourceFile(), $this->targetFile());

        // One of the two anchors has to be renamed. It does not matter which one
        $contents  = $this->getFileContents($this->targetFile('duplicateAnchor.html'));
        $contents .= $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString(' id="an-anchor-2"', $contents);
    }

    protected function configureExpectedWarnings(): void
    {
        $this->errorManager->expects(self::atLeastOnce())->method('warning');
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderDuplicateAnchorsInTwoFiles';
    }
}
