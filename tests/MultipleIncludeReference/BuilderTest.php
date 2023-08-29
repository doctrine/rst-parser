<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\MultipleIncludeReference;

use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;
use Doctrine\RST\Directives\CustomDirectiveFactory;
use Doctrine\RST\Kernel;
use PHPUnit\Framework\TestCase;

use function assert;
use function file_get_contents;

class BuilderTest extends TestCase
{
    public function testMultipleIncludeReference(): void
    {
        $kernel  = new Kernel(null, []);
        $builder = new Builder($kernel);
        $builder->getConfiguration()->setUseCachedMetas(false);

        $builder->build(
            __DIR__ . '/input',
            __DIR__ . '/output'
        );


        $expected = '<p>Reference is <a href="page1.html#reference-one">here</a></p>';
        $contents = file_get_contents(__DIR__ . '/output/page1.html');
        assert($contents !== false);
        self::assertStringContainsString(
            $expected,
            $contents
        );

        $expected = '<p>Reference is <a href="page2.html#reference-one">here</a></p>';
        $contents = file_get_contents(__DIR__ . '/output/page2.html');
        assert($contents !== false);
        self::assertStringContainsString(
            $expected,
            $contents
        );
    }
}
