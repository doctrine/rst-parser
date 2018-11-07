<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderInvalidReferences;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\Tests\RST\BaseBuilderTest;
use Throwable;

class BuilderInvalidReferencesTest extends BaseBuilderTest
{
    /** @var Configuration */
    private $configuration;

    protected function setUp() : void
    {
        $this->configuration = new Configuration();

        $this->builder = new Builder(null, $this->configuration);
    }

    public function testInvalidReference() : void
    {
        $this->expectException(Throwable::class);
        $this->expectExceptionMessage('Found invalid reference "does_not_exist" in file "index"');

        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    public function testInvalidReferenceIgnored() : void
    {
        $this->configuration->setIgnoreInvalidReferences(true);

        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('<p>Test unresolved reference</p>', $contents);
    }

    protected function getFixturesDirectory() : string
    {
        return 'BuilderInvalidReferences';
    }
}
