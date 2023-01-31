<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Toc\GlobSearcher;
use Doctrine\RST\Toc\ToctreeBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function ltrim;

class ToctreeBuilderTest extends TestCase
{
    /** @var GlobSearcher|MockObject */
    private $globSearcher;

    /** @var ToctreeBuilder */
    private $toctreeBuilder;

    public function testBuildToctreeFiles(): void
    {
        $environment = $this->createMock(Environment::class);
        $node        = $this->createMock(Node::class);
        $options     = ['glob' => true];

        $toc = <<<'EOF'
test1
*
test4
banana
EOF;

        $node->expects(self::once())
            ->method('getValueString')
            ->willReturn($toc);

        $this->globSearcher->expects(self::once())
            ->method('globSearch')
            ->with($environment, '*')
            ->willReturn(['/test1', '/test2', '/test3', '/apple', '/current_filename']);

        $environment
            ->method('getCurrentFileName')
            ->willReturn('current_filename');

        // expected to be called:
        // 3 times for "test1", "test4" then "banana"
        // 4 times for the globbed files (5 globbed files, but test1 is repeated & skipped)
        $environment->expects(self::exactly(7))
            ->method('absoluteUrl')
            ->willReturnCallback(static fn ($arg): string => '/' . ltrim($arg, '/'));

        $toctreeFiles = $this->toctreeBuilder
            ->buildToctreeFiles($environment, $node, $options);

        $expected = [
            '/test1',
            // alphabetically sorted glob
            '/apple',
            // /current_filename is missing: "own" files are not included in toc
            '/test2',
            '/test3',
            '/test4',
            '/banana',
        ];

        self::assertSame($expected, $toctreeFiles);
    }

    protected function setUp(): void
    {
        $this->globSearcher = $this->createMock(GlobSearcher::class);

        $this->toctreeBuilder = new ToctreeBuilder($this->globSearcher);
    }
}
