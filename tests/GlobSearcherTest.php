<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\Toc\GlobSearcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Path;

use function sort;

class GlobSearcherTest extends TestCase
{
    /** @var GlobSearcher */
    private $globSearcher;

    public function testGlobSearch(): void
    {
        $dir = Path::normalize(__DIR__) . '/BuilderToctree/input';

        $environment = $this->createMock(Environment::class);

        $environment->expects(self::once())
            ->method('absoluteRelativePath')
            ->with('')
            ->willReturn($dir);

        $environment->expects(self::once())
            ->method('getDirName')
            ->willReturn('subdir');

        $files = $this->globSearcher->globSearch($environment, '*');

        self::assertCount(7, $files);

        $expected = [
            '/orphaned/file',
            '/index',
            '/subdir/toctree',
            '/wildcards/bugfix1',
            '/wildcards/feature1',
            '/wildcards/feature2',
            '/wildcards/index',
        ];

        sort($expected);
        sort($files);

        self::assertSame($expected, $files);
    }

    protected function setUp(): void
    {
        $this->globSearcher = new GlobSearcher();
    }
}
