<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Parser;

use Doctrine\RST\Parser;
use Doctrine\RST\Parser\LineDataParser;
use Doctrine\RST\Parser\Link;
use PHPUnit\Framework\TestCase;

class LineDataParserTest extends TestCase
{
    /** @var Parser */
    private $parser;

    /** @var LineDataParser */
    private $lineDataParser;

    /**
     * @param mixed $expected
     *
     * @dataProvider getTestLinks
     */
    public function testParseLink(string $line, $expected) : void
    {
        self::assertEquals($expected, $this->lineDataParser->parseLink($line));
    }

    /**
     * @return mixed[]
     */
    public function getTestLinks() : array
    {
        return [
            ['', null],
            ['test', null],
            ['.. test', null],
            ['.. _test', null],
            ['.. _test: https://www.google.com', new Link('test', 'https://www.google.com', Link::TYPE_LINK)],
            ['.. _`test`: https://www.google.com', new Link('test', 'https://www.google.com', Link::TYPE_LINK)],
            ['__ https://www.google.com', new Link('_', 'https://www.google.com', Link::TYPE_LINK)],
            ['.. _anchor:', new Link('anchor', '#anchor', Link::TYPE_ANCHOR)],
        ];
    }

    protected function setUp() : void
    {
        $this->parser = $this->createMock(Parser::class);

        $this->lineDataParser = new LineDataParser($this->parser);
    }
}
