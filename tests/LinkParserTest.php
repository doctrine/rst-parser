<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;
use function trim;

class LinkParserTest extends TestCase
{
    /** @var Parser */
    private $parser;

    public function testStandaloneLinkWithUnderscoreAtTheEnd() : void
    {
        $result = $this->parser->parse('http://www.google.com/test_')->render();

        self::assertSame('<p><a href="http://www.google.com/test_">http://www.google.com/test_</a></p>', trim($result));
    }

    public function testLinkWithUnderscore() : void
    {
        $result = $this->parser->parse('has_underscore_')->render();

        self::assertSame('<p><a href="#has-underscore">has_underscore</a></p>', trim($result));
    }

    protected function setUp() : void
    {
        $this->parser = new Parser();
    }
}
