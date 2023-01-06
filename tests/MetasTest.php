<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\ErrorManager;
use Doctrine\RST\Meta\LinkTarget;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MetasTest extends TestCase
{
    /** @var ErrorManager|MockObject */
    private ErrorManager $errorManager;

    protected function setUp(): void
    {
        $this->errorManager = $this->createMock(ErrorManager::class);
    }

    public function testFindLinkMetaEntry(): void
    {
        $entry1 = new MetaEntry(
            'test.rst',
            'test.html',
            'Test',
            [],
            [],
            [],
            [
                'link1' => new LinkTarget('link1', '/link1'),
                'link2' => new LinkTarget('link2', '/link2'),
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
                'link3' => new LinkTarget('link3', '/link3'),
                'link4' => new LinkTarget('link4', '/link4'),
            ],
            0
        );

        $metas = new Metas(
            $this->errorManager,
            [
                $entry1,
                $entry2,
            ]
        );

        self::assertSame($entry1, $metas->findLinkTargetMetaEntry('link1'));
        self::assertSame($entry1, $metas->findLinkTargetMetaEntry('link2'));
        self::assertSame($entry2, $metas->findLinkTargetMetaEntry('link3'));
        self::assertSame($entry2, $metas->findLinkTargetMetaEntry('link4'));
    }
}
