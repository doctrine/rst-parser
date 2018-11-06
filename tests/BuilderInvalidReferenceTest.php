<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Throwable;

class BuilderInvalidReferenceTest extends BaseBuilderTest
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
        $this->expectExceptionMessage('Found invalid reference "#does_not_exist" in file "index"');

        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    public function testInvalidReferenceIgnored() : void
    {
        $this->configuration->setIgnoreInvalidReferences(true);

        $this->builder->build($this->sourceFile(), $this->targetFile());

        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertContains('<a href="#does_not_exist">unresolved reference</a>', $contents);
    }

    protected function getFixturesDirectory() : string
    {
        return 'builder-invalid-reference';
    }
}
