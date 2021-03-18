<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderReferenceDoesNotExist;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderReferenceDoesNotExistTest extends BaseBuilderTest
{
    /** @var Configuration */
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setUseCachedMetas(false);
        $this->configuration->abortOnError(false);
        $this->configuration->silentOnError(true);
        $this->configuration->setIgnoreInvalidReferences(false);

        $this->builder = new Builder(new Kernel($this->configuration));
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
