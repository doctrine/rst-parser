<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\BuilderUnknownDirective;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use Doctrine\Tests\RST\BaseBuilderTest;

class BuilderUnknownDirectiveTest extends BaseBuilderTest
{
    /** @var Configuration */
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setUseCachedMetas(false);
        $this->configuration->silentOnError(true);
        $this->configuration->abortOnError(false);

        $this->builder = new Builder(new Kernel($this->configuration));
    }

    public function testUnknownDirectiveWithFieldOptions(): void
    {
        $this->builder->build($this->sourceFile(), $this->targetFile());
        $contents = $this->getFileContents($this->targetFile('index.html'));
        self::assertStringNotContainsString('<p>Test link 2 to</p>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'BuilderUnknownDirective';
    }
}
