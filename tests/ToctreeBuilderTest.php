<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\GlobSearcher;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\ToctreeBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ToctreeBuilderTest extends TestCase
{
    /** @var GlobSearcher|MockObject */
    private $globSearcher;

    /** @var ToctreeBuilder */
    private $toctreeBuilder;

    public function testBuildToctreeFiles() : void
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

        $environment->expects(self::at(0))
            ->method('absoluteUrl')
            ->with('test1')
            ->willReturn('/test1');

        $this->globSearcher->expects(self::once())
            ->method('globSearch')
            ->with($environment, '*')
            ->willReturn(['/test1', '/test2', '/test3']);

        $environment->expects(self::at(1))
            ->method('absoluteUrl')
            ->with('test4')
            ->willReturn('/test4');

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

    protected function setUp() : void
    {
        $this->globSearcher = $this->createMock(GlobSearcher::class);

        $this->toctreeBuilder = new ToctreeBuilder($this->globSearcher);
    }
}
