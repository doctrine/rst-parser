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
        self::assertEquals($environment->relativeUrl('test.jpg'), 'test.jpg');
        self::assertEquals($environment->relativeUrl('/path/to/test.jpg'), 'test.jpg');
        self::assertEquals($environment->relativeUrl('/path/x/test.jpg'), '../../path/x/test.jpg');
        self::assertEquals($environment->relativeUrl('/test.jpg'), '../../test.jpg');
        self::assertEquals($environment->relativeUrl('http://example.com/test.jpg'), 'http://example.com/test.jpg');
        self::assertEquals($environment->relativeUrl('imgs/test.jpg'), 'imgs/test.jpg');
        self::assertEquals($environment->relativeUrl('/imgs/test.jpg'), '../../imgs/test.jpg');
    }
}
