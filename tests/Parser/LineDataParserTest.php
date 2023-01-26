<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Parser;

use Doctrine\Common\EventManager;
use Doctrine\RST\Parser;
use Doctrine\RST\Parser\LineDataParser;
use Doctrine\RST\Parser\Link;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function explode;

class LineDataParserTest extends TestCase
{
    /** @var Parser */
    private $parser;

    /** @var LineDataParser */
    private $lineDataParser;

    /** @var EventManager|MockObject $eventManager */
    private $eventManager;

    /**
     * @param mixed $expected
     *
     * @dataProvider getTestLinks
     */
    public function testParseLink(string $line, $expected): void
    {
        $this->eventManager->expects(self::exactly($expected instanceof Link ? 1 : 0))
            ->method('dispatchEvent');

        self::assertEquals($expected, $this->lineDataParser->parseLinkTarget($line));
    }

    /** @return list<array{string, mixed}> */
    public function getTestLinks(): array
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

    /**
     * @param true|string $body
     *
     * @dataProvider getTestFieldOptions
     */
    public function testParseFieldOptions(string $line, ?string $name, $body = null): void
    {
        $actual = $this->lineDataParser->parseFieldOption($line);
        if ($name === null) {
            self::assertNull($actual);
        } else {
            self::assertNotNull($actual);
            self::assertSame($name, $actual->getName(), 'Incorrect field option name');
            self::assertSame($body, $actual->getBody(), 'Incorrect field option body');
        }
    }

    /** @return array{string, string|null, true|string}[] */
    public function getTestFieldOptions(): array
    {
        return [
            [':glob:', 'glob', ''],
            [':alt: Some text', 'alt', 'Some text'],
            [':date:published: 2022-09-20', 'date:published', '2022-09-20'],
            [':date\: published: 2022-09-20', 'date: published', '2022-09-20'],
            [':date: published: 2022-09-20', 'date', 'published: 2022-09-20'],
        ];
    }

    public function testParseFieldList(): void
    {
        $data         = <<<'EOD'
:what:  Field lists map field names to field bodies, like
        database records.  They are often part of an extension
        syntax.

:how:   The field marker is a colon, the field name, and a
        colon.

        The field body may contain one or more body elements,
        indented relative to the field marker.
EOD;
        $lines        = explode("\n", $data);
        $fieldOptions = $this->lineDataParser->parseFieldList($lines);
        self::assertCount(2, $fieldOptions);
        self::assertEquals('what', $fieldOptions[0]->getName());
        self::assertEquals('how', $fieldOptions[1]->getName());
    }

    protected function setUp(): void
    {
        $this->parser = $this->createMock(Parser::class);

        $this->eventManager = $this->createMock(EventManager::class);

        $this->lineDataParser = new LineDataParser($this->parser, $this->eventManager);
    }
}
