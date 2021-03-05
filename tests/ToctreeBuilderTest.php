<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Toc\GlobSearcher;
use Doctrine\RST\Toc\ToctreeBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

        $toc = <<<EOF
test1
*
test4
EOF;

        $node->expects(self::once())
            ->method('getValueString')
            ->willReturn($toc);

        $environment->expects($this->exactly(2))
            ->method('absoluteUrl')
            ->withConsecutive(['test1'], ['test4'])
            ->willReturnCallback(static function ($arg) {
                return '/' . $arg;
            });

        $this->globSearcher->expects(self::once())
            ->method('globSearch')
            ->with($environment, '*')
            ->willReturn(['/test1', '/test2', '/test3']);

        $toctreeFiles = $this->toctreeBuilder
            ->buildToctreeFiles($environment, $node, $options);

        $expected = [
            '/test1',
            '/test2',
            '/test3',
            '/test4',
        ];

        self::assertSame($expected, $toctreeFiles);
    }

    protected function setUp(): void
    {
        $this->globSearcher = $this->createMock(GlobSearcher::class);

        $this->toctreeBuilder = new ToctreeBuilder($this->globSearcher);
    }
}
