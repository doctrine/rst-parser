<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\RefInsideDirective;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use PHPUnit\Framework\TestCase;

use function assert;
use function file_get_contents;

class BuilderTest extends TestCase
{
    public function testRefInsideDirective(): void
    {
        $configuration = new Configuration();
        $kernel        = new Kernel($configuration, [new VersionAddedDirective()]);
        $builder       = new Builder($configuration, $kernel);
        $builder->getConfiguration()->setUseCachedMetas(false);

        $builder->build(
            __DIR__ . '/input',
            __DIR__ . '/output'
        );

        $expected = 'Test a reference in a directive <a href="file.html#some_reference">A file</a>.';
        $contents = file_get_contents(__DIR__ . '/output/index.html');
        assert($contents !== false);
        self::assertStringContainsString(
            $expected,
            $contents
        );
    }
}
