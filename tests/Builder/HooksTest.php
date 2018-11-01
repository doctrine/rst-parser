<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Builder;

use Doctrine\RST\Builder\Hooks;
use Doctrine\RST\Document;
use Doctrine\RST\Parser;
use PHPUnit\Framework\TestCase;

class HooksTest extends TestCase
{
    /** @var Hooks */
    private $hooks;

    public function testCallHooks() : void
    {
        $called = null;

        $this->hooks->addHook(static function (Document $document) use (&$called) : void {
            $called = $document;
        });

        $document = $this->createMock(Document::class);

        $this->hooks->callHooks($document);

        self::assertSame($document, $called);
    }

    public function testCallBeforeHooks() : void
    {
        $called = null;

        $this->hooks->addBeforeHook(static function (Parser $parser) use (&$called) : void {
            $called = $parser;
        });

        $parser = $this->createMock(Parser::class);

        $this->hooks->callBeforeHooks($parser);

        self::assertSame($parser, $called);
    }

    protected function setUp() : void
    {
        $this->hooks = new Hooks();
    }
}
