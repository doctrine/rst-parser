<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\HTML;

use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use Doctrine\RST\HTML\Renderers\SpanNodeRenderer;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;

class SpanTest extends TestCase
{
    /**
     * @param string[] $attributes
     *
     * @dataProvider linkProvider
     */
    public function testLink(string $url, string $title, array $attributes, string $expectedLink): void
    {
        $parser      = $this->createMock(Parser::class);
        $environment = $this->createMock(Environment::class);

        $parser->expects(self::once())
            ->method('getEnvironment')
            ->willReturn($environment);

        $environment->expects(self::once())
            ->method('generateUrl')
            ->with($url)
            ->willReturn($url);

        $configuration    = new Configuration();
        $templateRenderer = $configuration->getTemplateRenderer();

        $span         = new SpanNode($parser, 'span');
        $spanRenderer = new SpanNodeRenderer($environment, $span, $templateRenderer);

        self::assertSame(
            $expectedLink,
            $spanRenderer->link($url, $title, $attributes)
        );
    }

    /** @return string[][]|string[][][] */
    public function linkProvider(): array
    {
        return [
            'no attributes #1' => [
                'url'          => '#',
                'title'        => 'link',
                'attributes'   => [],
                'expectedLink' => '<a href="#">link</a>',
            ],

            'no attributes #2' => [
                'url'          => '/url?foo=bar&bar=foo',
                'title'        => 'link',
                'attributes'   => [],
                'expectedLink' => '<a href="/url?foo=bar&bar=foo">link</a>',
            ],

            'no attributes #3' => [
                'url'          => 'https://www.doctrine-project.org/',
                'title'        => 'link',
                'attributes'   => [],
                'expectedLink' => '<a href="https://www.doctrine-project.org/">link</a>',
            ],

            'with attributes #1' => [
                'url'          => '/url',
                'title'        => 'link',
                'attributes'   => ['class' => 'foo bar'],
                'expectedLink' => '<a href="/url" class="foo bar">link</a>',
            ],

            'with attributes #2' => [
                'url'          => '/url',
                'title'        => 'link',
                'attributes'   => ['class' => 'foo <>bar'],
                'expectedLink' => '<a href="/url" class="foo &lt;&gt;bar">link</a>',
            ],

            'with attributes #3' => [
                'url'          => '/url',
                'title'        => 'link',
                'attributes'   => ['class' => 'foo bar', 'data-id' => '123456'],
                'expectedLink' => '<a href="/url" class="foo bar" data-id="123456">link</a>',
            ],
        ];
    }
}
