<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use Doctrine\RST\Builder\ParseQueue;
use Doctrine\RST\Builder\Scanner;
use Doctrine\RST\Builder\State;
use Doctrine\RST\Meta\MetaEntry;
use Doctrine\RST\Meta\Metas;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScannerTest extends TestCase
{
    /** @var ParseQueue|MockObject */
    private $parseQueue;

    /** @var Metas|MockObject */
    private $metas;

    /** @var Scanner */
    private $scanner;

    public function testScan() : void
    {
        $this->parseQueue->expects(self::once())
            ->method('getState')
            ->with('file')
            ->willReturn(null);

        $this->parseQueue->expects(self::once())
            ->method('setState')
            ->with('file', State::NO_PARSE);

        $metaEntry = $this->createMock(MetaEntry::class);

        $this->metas->expects(self::once())
            ->method('get')
            ->with('file')
            ->willReturn($metaEntry);

        $this->parseQueue->expects(self::once())
            ->method('addToParseQueue')
            ->with('file');

        $this->scanner->scan('/directory', 'file');
    }

    public function testScanMetaEntry() : void
    {
        $metaEntry = $this->createMock(MetaEntry::class);

        $metaEntry->expects(self::once())
            ->method('getDepends')
            ->willReturn(['dependency']);

        $metaEntry->expects(self::once())
            ->method('getParent')
            ->willReturn('parent');

        $this->parseQueue->expects(self::at(0))
            ->method('getState')
            ->with('dependency')
            ->willReturn(State::PARSE);

        $this->parseQueue->expects(self::at(1))
            ->method('getState')
            ->with('dependency')
            ->willReturn(State::PARSE);

        $this->parseQueue->expects(self::at(2))
            ->method('addToParseQueue')
            ->with('file');

        $this->scanner->scanMetaEntry($metaEntry, '/directory', 'file');
    }

    public function testScanMetas() : void
    {
        $metaEntry = $this->createMock(MetaEntry::class);

        $metaEntries = ['file' => $metaEntry];

        $this->metas->expects(self::once())
            ->method('getAll')
            ->willReturn($metaEntries);

        $this->parseQueue->expects(self::once())
            ->method('getState')
            ->with('file')
            ->willReturn(null);

        $this->parseQueue->expects(self::once())
            ->method('setState')
            ->with('file', State::NO_PARSE);

        $this->metas->expects(self::once())
            ->method('get')
            ->with('file')
            ->willReturn($metaEntry);

        $this->parseQueue->expects(self::once())
            ->method('addToParseQueue')
            ->with('file');

        $this->scanner->scanMetas('/directory');
    }

    protected function setUp() : void
    {
        $this->parseQueue = $this->createMock(ParseQueue::class);
        $this->metas      = $this->createMock(Metas::class);

        $this->scanner = new Scanner($this->parseQueue, $this->metas);
    }
}
