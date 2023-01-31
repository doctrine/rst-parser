<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\TextRoles\ReferenceRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReferenceRoleTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;

    protected function setUp(): void
    {
        $this->environment = $this->createMock(Environment::class);
    }

    /** @dataProvider getTextsAndSlugs */
    public function testUrlProcessing(string $text, string $url, ?string $linktext, ?string $anchor): void
    {
        $textRole = $this->getMockForAbstractClass(ReferenceRole::class);
        $name     = 'ref';
        $textRole->method('getName')->willReturn($name);
        $concreteData = $textRole->process($this->environment, $text);

        self::assertArrayHasKey('url', $concreteData);
        self::assertEquals($url, $concreteData['url']);
        self::assertArrayHasKey('linktext', $concreteData);
        self::assertEquals($linktext, $concreteData['linktext']);
        self::assertArrayHasKey('anchor', $concreteData);
        self::assertEquals($anchor, $concreteData['anchor']);
        self::assertArrayHasKey('section', $concreteData);
        self::assertEquals($name, $concreteData['section']);
    }

    /** @return iterable<string, array{string, string, ?string}> */
    public function getTextsAndSlugs(): iterable
    {
        yield 'empty link text' => [
            'somereference',
            'somereference',
            null,
            null,
        ];

        yield 'link text set' => [
            'Go to <somereference>',
            'somereference',
            'Go to',
            null,
        ];

        yield 'special signs in Linktext' => [
            'See SomeClass::goHere() <somereference>',
            'somereference',
            'See SomeClass::goHere()',
            null,
        ];

        yield 'link with anchor' => [
            'somereference#myanchor',
            'somereference',
            null,
            'myanchor',
        ];

        yield 'link with anchor and link text' => [
            'See here <somereference#myanchor>',
            'somereference',
            'See here',
            'myanchor',
        ];
    }
}
