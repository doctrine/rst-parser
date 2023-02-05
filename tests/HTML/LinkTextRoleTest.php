<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\HTML;

use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use Doctrine\RST\HTML\TextRoles\LinkTextRole;
use PHPUnit\Framework\TestCase;

class LinkTextRoleTest extends TestCase
{
    /**
     * @param string[] $attributes
     *
     * @dataProvider linkProvider
     */
    public function testLink(string $url, string $title, array $attributes, string $expectedLink): void
    {
        $environment  = $this->createMock(Environment::class);
        $linkTextRole = new LinkTextRole();

        $configuration    = new Configuration();
        $templateRenderer = $configuration->getTemplateRenderer();

        $environment->expects(self::once())
            ->method('generateUrl')
            ->with($url)
            ->willReturn($url);

        $environment->expects(self::once())
            ->method('getTemplateRenderer')
            ->willReturn($templateRenderer);

        self::assertSame(
            $expectedLink,
            $linkTextRole->renderLink($environment, $url, $title, $attributes)
        );
    }

    /**
     * @return array<string, array{
     *     url: string,
     *     title: string,
     *     attributes: array<string, string>,
     *     expectedLink: string
     * }>
     */
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
