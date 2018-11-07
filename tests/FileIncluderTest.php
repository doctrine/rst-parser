<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\FileIncluder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use function trim;

class FileIncluderTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;

    public function testInclude() : void
    {
        $this->environment->expects(self::once())
            ->method('absoluteRelativePath')
            ->with('include.rst')
            ->willReturn(__DIR__ . '/Parser/files/include.rst');

        $fileIncluder = new FileIncluder($this->environment, true, __DIR__ . '/Parser/files');

        $contents = $fileIncluder->includeFiles('.. include:: include.rst');

        self::assertSame('I was actually included', trim($contents));
    }

    public function testIncludeWithEmptyIncludeRoot() : void
    {
        $this->environment->expects(self::once())
            ->method('absoluteRelativePath')
            ->with('include.rst')
            ->willReturn(__DIR__ . '/Parser/files/include.rst');

        $fileIncluder = new FileIncluder($this->environment, true, '');

        $contents = $fileIncluder->includeFiles('.. include:: include.rst');

        self::assertSame('I was actually included', trim($contents));
    }

    public function testShouldThrowExceptionOnInvalidFileInclude() : void
    {
        $this->environment->expects(self::once())
            ->method('absoluteRelativePath')
            ->with('non-existent-file.rst')
            ->willReturn('non-existent-file.rst');

        $fileIncluder = new FileIncluder($this->environment, true, '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Include ".. include:: non-existent-file.rst" does not exist or is not readable.');

        $fileIncluder->includeFiles('.. include:: non-existent-file.rst');
    }

    protected function setUp() : void
    {
        $this->environment = $this->createMock(Environment::class);
    }
}
