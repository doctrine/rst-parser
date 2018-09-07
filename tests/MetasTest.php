<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\MetaEntry;
use Doctrine\RST\Metas;
use PHPUnit\Framework\TestCase;

class MetasTest extends TestCase
{
    public function testFindLinkMetaEntry() : void
    {
        $entry1 = new MetaEntry(
            'test.rst',
            'test.html',
            'Test',
            [],
            [],
            [],
            [
                'link1' => '/link1',
                'link2' => '/link2',
            ],
            0
        );

        $entry2 = new MetaEntry(
            'test.rst',
            'test.html',
            'Test',
            [],
            [],
            [],
            [
                'link3' => '/link3',
                'link4' => '/link4',
            ],
            0
        );

        $metas = new Metas([
            $entry1,
            $entry2,
        ]);

        self::assertSame($entry1, $metas->findLinkMetaEntry('link1'));
        self::assertSame($entry1, $metas->findLinkMetaEntry('link2'));
        self::assertSame($entry2, $metas->findLinkMetaEntry('link3'));
        self::assertSame($entry2, $metas->findLinkMetaEntry('link4'));
    }
}
