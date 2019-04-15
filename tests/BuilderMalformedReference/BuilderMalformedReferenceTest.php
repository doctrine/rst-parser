<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderInvalidReferences;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use Doctrine\Tests\RST\BaseBuilderTest;
use Throwable;

class BuilderMalformedReferenceTest extends BaseBuilderTest
{
    /** @var Configuration */
    private $configuration;

    protected function setUp() : void
    {
        $this->configuration = new Configuration();
        $this->configuration->setUseCachedMetas(false);
        $this->configuration->abortOnError(false);

        $this->builder = new Builder(new Kernel($this->configuration));
    }

    public function testMalformedReference() : void
    {
        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    protected function getFixturesDirectory() : string
    {
        return 'BuilderMalformedReference';
    }
}
