<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use PHPUnit\Framework\TestCase;

/**
 * Unit testing for RST
 */
class EnvironmentTest extends TestCase
{
    public function testRelativeUrl() : void
    {
        $environment = new Environment();
        $environment->setCurrentFileName('path/to/something.rst');
        $environment->setCurrentDirectory('input/dir');

        // Assert that rules of relative url are respected
        self::assertSame($environment->relativeUrl('test.jpg'), 'test.jpg');
        self::assertSame($environment->relativeUrl('/path/to/test.jpg'), 'test.jpg');
        self::assertSame($environment->relativeUrl('/path/x/test.jpg'), '../../path/x/test.jpg');
        self::assertSame($environment->relativeUrl('/test.jpg'), '../../test.jpg');
        self::assertSame($environment->relativeUrl('http://example.com/test.jpg'), 'http://example.com/test.jpg');
        self::assertSame($environment->relativeUrl('imgs/test.jpg'), 'imgs/test.jpg');
        self::assertSame($environment->relativeUrl('/imgs/test.jpg'), '../../imgs/test.jpg');
    }
}
