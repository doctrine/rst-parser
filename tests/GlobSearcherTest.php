<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\Toc\GlobSearcher;
use PHPUnit\Framework\TestCase;
use function sort;

class GlobSearcherTest extends TestCase
{
    /** @var GlobSearcher */
    private $globSearcher;

    public function testGlobSearch() : void
    {
        $dir = __DIR__ . '/BuilderToctree/input';

        $environment = $this->createMock(Environment::class);

        $environment->expects(self::once())
            ->method('absoluteRelativePath')
            ->with('')
            ->willReturn($dir);

        $environment->expects(self::once())
            ->method('getDirName')
            ->willReturn('subdir');

        $files = $this->globSearcher->globSearch($environment, '*');

        self::assertCount(3, $files);

        $expected = [
            '/orphaned/file',
            '/index',
            '/subdir/toctree',
        ];

        sort($expected);
        sort($files);

        self::assertSame($expected, $files);
    }

    protected function setUp() : void
    {
        $this->globSearcher = new GlobSearcher();
    }
}
