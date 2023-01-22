<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\LiteralNestedInDirective;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Directives\CustomDirectiveFactory;
use Doctrine\Tests\RST\BaseBuilderTest;

use function shell_exec;

/**
 * Unit testing for RST
 */
class BuilderTest extends BaseBuilderTest
{
    protected function setUp(): void
    {
        shell_exec('rm -rf ' . $this->targetFile());
        $configuration = new Configuration();

        $configuration->addDirectiveFactory(new CustomDirectiveFactory([new TipDirective()]));

        $this->builder = new Builder($configuration);
        $this->builder->getConfiguration()->setUseCachedMetas(false);

        $this->builder->build($this->sourceFile(), $this->targetFile());
    }

    public function testLiteralNestedInDirective(): void
    {
        $contents = $this->getFileContents($this->targetFile('index.html'));

        self::assertStringContainsString('class="tip"', $contents);
        self::assertStringContainsString('<code', $contents);
        self::assertStringContainsString('</code>', $contents);
    }

    protected function getFixturesDirectory(): string
    {
        return 'LiteralNestedInDirective';
    }
}
