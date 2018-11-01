<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use Doctrine\RST\Builder\Documents;
use Doctrine\RST\Builder\ParseQueue;
use Doctrine\RST\Builder\State;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ParseQueueTest extends TestCase
{
    /** @var Documents|MockObject */
    private $documents;

    /** @var ParseQueue */
    private $parseQueue;

    public function testGetSetState() : void
    {
        self::assertNull($this->parseQueue->getState('file'));

        $this->parseQueue->setState('file', State::NO_PARSE);

        self::assertSame(State::NO_PARSE, $this->parseQueue->getState('file'));
    }

    public function testGetFileToParse() : void
    {
        self::assertNull($this->parseQueue->getFileToParse());

        $this->parseQueue->addToParseQueue('file1');
        $this->parseQueue->addToParseQueue('file2');

        self::assertSame('file1', $this->parseQueue->getFileToParse());
        self::assertSame('file2', $this->parseQueue->getFileToParse());
        self::assertNull($this->parseQueue->getFileToParse());
    }

    public function testAddToParseQueue() : void
    {
        $this->documents->expects(self::once())
            ->method('hasDocument')
            ->with('file')
            ->willReturn(true);

        $this->parseQueue->addToParseQueue('file');

        self::assertNull($this->parseQueue->getFileToParse());
    }

    protected function setUp() : void
    {
        $this->documents = $this->createMock(Documents::class);

        $this->parseQueue = new ParseQueue($this->documents);
    }
}
