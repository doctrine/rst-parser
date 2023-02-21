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

    public function testGlobSearch(): void
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

        self::assertCount(19, $files);

        $expected = [
            '/orphaned/file',
            '/index',
            '/subdir/toctree',
            '/testMaxDepth1',
            '/testMaxDepth2',
            '/testMaxDepth3',
            '/testNoMaxDepth',
            '/wildcards/bugfix1',
            '/wildcards/feature1',
            '/wildcards/feature2',
            '/wildcards/index',
            '/level1-1/index',
            '/level1-2/index',
            '/level1-1/level2-1/index',
            '/level1-1/level2-2/index',
            '/level1-1/level2-1/level3-1/index',
            '/level1-1/level2-1/level3-2/index',
            '/level1-1/level2-1/level3-1/level4-1/index',
            '/level1-1/level2-1/level3-1/level4-2/index',
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
