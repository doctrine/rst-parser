<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\RefInsideDirective;

use Doctrine\RST\Builder;
use PHPUnit\Framework\TestCase;
use function file_get_contents;

class BuilderTest extends TestCase
{
    public function testRefInsideDirective() : void
    {
        $kernel  = new HtmlKernel();
        $builder = new Builder($kernel);

        $builder->build(
            __DIR__ . '/rst',
            __DIR__ . '/output'
        );

        $expected = 'Test a reference in a directive <a href="file.html#some_reference">A file</a>.';

        self::assertContains($expected, file_get_contents(__DIR__ . '/output/index.html'));
    }
}
