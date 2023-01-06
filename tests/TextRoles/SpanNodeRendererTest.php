<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Renderers\SpanNodeRenderer;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Span\SpanToken;
use Doctrine\RST\Templates\TemplateRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpanNodeRendererTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;
    /** @var SpanNode|MockObject */
    private $spanNode;
    /** @var TemplateRenderer|MockObject */
    private $templateRenderer;

    private SpanNodeRenderer $spanNodeRenderer;

    protected function setUp(): void
    {
        $this->environment      = $this->createMock(Environment::class);
        $this->spanNode         = $this->createMock(SpanNode::class);
        $this->templateRenderer = $this->createMock(TemplateRenderer::class);
        $this->spanNodeRenderer = new SpanNodeRenderer($this->environment, $this->spanNode, $this->templateRenderer);
    }

    public function testExampleRoleWrapsContent(): void
    {
        $expected = '<samp>Example</samp>';
        $this->spanNode->method('getTokens')->willReturn([new SpanToken(SpanToken::TYPE_TEXT_ROLE, 'id123', ['url' => 'Example'])]);
        $this->spanNode->method('getValue')->willReturn('id123');
        $this->environment->method('getTextRole')->willReturn(new ExampleRole());
        self::assertEquals($expected, $this->spanNodeRenderer->render());
    }

    public function testUnkownRole(): void
    {
        $expected = 'Example';
        $this->spanNode->method('getTokens')->willReturn([new SpanToken(SpanToken::TYPE_TEXT_ROLE, 'id123', ['url' => 'Example'])]);
        $this->spanNode->method('getValue')->willReturn('id123');
        $this->environment->method('getTextRole')->willReturn(null);
        self::assertEquals($expected, $this->spanNodeRenderer->render());
    }
}
