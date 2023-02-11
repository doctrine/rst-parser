<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Renderers\SpanNodeRenderer;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Span\SpanToken;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpanNodeRendererTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;
    /** @var SpanNode|MockObject */
    private $spanNode;

    private SpanNodeRenderer $spanNodeRenderer;

    protected function setUp(): void
    {
        $this->environment      = $this->createMock(Environment::class);
        $this->spanNode         = $this->createMock(SpanNode::class);
        $this->spanNodeRenderer = new SpanNodeRenderer($this->environment, $this->spanNode);
    }

    public function testExampleRoleWrapsContent(): void
    {
        $expected = '<samp>Example</samp>';
        $this->spanNode->method('getTokens')->willReturn([new SpanToken(new ExampleRole(), 'id123', ['text' => 'Example'])]);
        $this->spanNode->method('getValue')->willReturn('id123');
        self::assertEquals($expected, $this->spanNodeRenderer->render());
    }

    public function testUnkownRole(): void
    {
        $expected = 'Example';
        $this->spanNode->method('getTokens')->willReturn([new SpanToken(null, 'id123', ['text' => 'Example'])]);
        $this->spanNode->method('getValue')->willReturn('id123');
        self::assertEquals($expected, $this->spanNodeRenderer->render());
    }
}
