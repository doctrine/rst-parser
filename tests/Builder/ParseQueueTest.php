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
    public function testAddingFiles()
    {
        $parseQueue = new ParseQueue();
        $parseQueue->addFile('file_needs_parsing1', true);
        $parseQueue->addFile('file_no_parsing1', false);
        
        $this->assertTrue($parseQueue->isFileKnownToParseQueue('file_needs_parsing1'));
        $this->assertTrue($parseQueue->isFileKnownToParseQueue('file_no_parsing1'));
        $this->assertFalse($parseQueue->isFileKnownToParseQueue('other_file'));

        $this->assertTrue($parseQueue->doesFileRequireParsing('file_needs_parsing1'));
        $this->assertFalse($parseQueue->doesFileRequireParsing('file_no_parsing1'));

        $this->assertSame(['file_needs_parsing1'], $parseQueue->getAllFilesThatRequireParsing());
    }
}
