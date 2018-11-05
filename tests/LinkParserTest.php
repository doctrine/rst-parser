<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;

class LinkParserTest extends TestCase
{
    public function testStandaloneLink() : void
    {
        $parser = new Parser();

        $result = $parser->parse('This is url with an underscore in it: http://www.google.com/test_')->render();

        self::assertSame('<p>This is url with an underscore in it: <a href="http://www.google.com/test_">http://www.google.com/test_</a></p>', $result);
    }
}
