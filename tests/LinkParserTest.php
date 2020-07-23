<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use Doctrine\RST\Kernel;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;
use function trim;

class LinkParserTest extends TestCase
{
    /** @var Configuration */
    private $configuration;

    /** @var Parser */
    private $parser;

    public function testStandaloneLinkWithUnderscoreAtTheEnd() : void
    {
        $result = $this->parser->parse('http://www.google.com/test_')->render();

        self::assertSame('<p><a href="http://www.google.com/test_">http://www.google.com/test_</a></p>', trim($result));
    }

    public function testLinkWithUnderscore() : void
    {
        $rst = <<<EOF
has_underscore_

.. _has_underscore: https://www.google.com
EOF;

        $result = $this->parser->parse($rst)->render();

        self::assertSame('<p><a href="https://www.google.com">has_underscore</a></p>', trim($result));
    }

    public function testInvalidLinks() : void
    {
        $this->configuration->setIgnoreInvalidReferences(true);
        $this->configuration->abortOnError(false);

        $rst = <<<EOF
does_not_exist_

`Does Not Exist1`_

:ref:`Does Not Exist2 <does-not-exist2>`
EOF;

        $result = $this->parser->parse($rst)->render();

        self::assertContains('<p>does_not_exist</p>', $result);
        self::assertContains('<p>Does Not Exist1</p>', $result);
        self::assertContains('<p>Does Not Exist2</p>', $result);
    }

    public function testLinkWithParenthesis() : void
    {
        $rst = <<<EOF
Example_, `Example Web Site`_, Example (`Web Site`_), and Example (Link_)

.. _Example: http://www.example.com/
.. _`Example Web Site`: http://www.example.com/
.. _`Web Site`: http://www.example.com/
.. _Link: http://www.example.com/
EOF;

        $result = $this->parser->parse($rst)->render();

        self::assertSame('<p><a href="http://www.example.com/">Example</a>, <a href="http://www.example.com/">Example Web Site</a>, Example (<a href="http://www.example.com/">Web Site</a>), and Example (<a href="http://www.example.com/">Link</a>)</p>', trim($result));
    }

    protected function setUp() : void
    {
        $this->configuration = new Configuration();

        $this->parser = new Parser(new Kernel($this->configuration));
    }
}
