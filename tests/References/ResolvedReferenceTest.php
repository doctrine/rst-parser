<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\References;

use Doctrine\RST\References\ResolvedReference;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function key;
use function sprintf;

class ResolvedReferenceTest extends TestCase
{
    /**
     * @param string[] $attributes
     *
     * @dataProvider attributesValid
     */
    public function testCreateResolvedReference(array $attributes): void
    {
        $resolvedReference = new ResolvedReference('file', 'title', 'url', [['title' => 'title']], $attributes);

        self::assertSame('title', $resolvedReference->getTitle());
        self::assertSame('url', $resolvedReference->getUrl());
        self::assertSame([['title' => 'title']], $resolvedReference->getTitles());
        self::assertSame($attributes, $resolvedReference->getAttributes());
    }

    /** @return string[][]|string[][][] */
    public function attributesValid(): array
    {
        return [
            'attributes #1' => [
                'attributes' => [],
            ],

            'attributes #2' => [
                'attributes' => ['foo' => 'bar'],
            ],

            'attributes #3' => [
                'attributes' => [
                    'class'   => 'foo bar',
                    'data-id' => '123456',
                ],
            ],

            'attributes #4' => [
                'attributes' => [
                    'foo123_.'      => 'bar',
                    '_foo123_.-foo' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @param string[] $attributes
     *
     * @dataProvider attributesInvalid
     */
    public function testCreateResolvedReferenceWithAttributesInvalid(array $attributes): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage(sprintf('Attribute with name "%s" is not allowed', key($attributes)));

        new ResolvedReference('file', 'title', 'url', [], $attributes);
    }

    /** @return string[][]|string[][][] */
    public function attributesInvalid(): array
    {
        return [
            'href' => [
                'attributes' => ['href' => 'foo'],
            ],

            'illegal char #1' => [
                'attributes' => ['attri"bute' => 'foo'],
            ],

            'illegal char #2' => [
                'attributes' => ['attri#bute' => 'foo'],
            ],

            'illegal char #3' => [
                'attributes' => ['attri"bute' => 'foo'],
            ],

            'illegal char #4' => [
                'attributes' => ['attri\'bute' => 'foo'],
            ],

            'illegal char #5' => [
                'attributes' => ['attri&bute' => 'foo'],
            ],

            'starts with illegal char #1' => [
                'attributes' => ['1attribute' => 'foo'],
            ],

            'starts with illegal char #2' => [
                'attributes' => ['-attribute' => 'foo'],
            ],

            'starts with illegal char #3' => [
                'attributes' => ['.attribute' => 'foo'],
            ],

            'starts with illegal char #4' => [
                'attributes' => ['#attribute' => 'foo'],
            ],

            'starts with illegal char #5' => [
                'attributes' => ['"attribute' => 'foo'],
            ],

            'non string' => [
                'attributes' => [5 => 'foo'],
            ],
        ];
    }
}
