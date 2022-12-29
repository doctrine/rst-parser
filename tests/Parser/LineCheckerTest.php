<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\Parser;

use Doctrine\Common\EventManager;
use Doctrine\RST\Parser\LineChecker;
use PHPUnit\Framework\TestCase;

use function str_repeat;

class LineCheckerTest extends TestCase
{
    /** @var LineChecker */
    private $lineChecker;

    protected function setUp(): void
    {
        $eventManager = $this->createMock(EventManager::class);

        $this->lineChecker = new LineChecker();
    }

    /** @dataProvider getSpecialCharacters */
    public function testIsSpecialLine(string $specialCharacter): void
    {
        self::assertNull($this->lineChecker->isSpecialLine($specialCharacter));
        self::assertSame($specialCharacter, $this->lineChecker->isSpecialLine(str_repeat($specialCharacter, 2)));
        self::assertSame($specialCharacter, $this->lineChecker->isSpecialLine(str_repeat($specialCharacter, 3)));
    }

    /** @return list<array{string}> */
    public function getSpecialCharacters(): array
    {
        return [['='], ['-'], ['~'], ['*'], ['+'], ['^'], ['"'], ['.'], ['`'], ["'"], ['_'], ['#'], [':']];
    }

    public function testIsListLine(): void
    {
        self::assertTrue($this->lineChecker->isListLine('- Test'));
        self::assertTrue($this->lineChecker->isListLine('- Test'));
        self::assertFalse($this->lineChecker->isListLine(' - Test'));
    }

    public function testIsBlockLine(): void
    {
        self::assertTrue($this->lineChecker->isBlockLine(' '));
        self::assertTrue($this->lineChecker->isBlockLine('  '));
        self::assertTrue($this->lineChecker->isBlockLine('   '));
        self::assertTrue($this->lineChecker->isBlockLine(''));
        self::assertFalse($this->lineChecker->isBlockLine('- Test'));
        self::assertFalse($this->lineChecker->isBlockLine('.. code-block::'));
    }

    public function testIsComment(): void
    {
        self::assertTrue($this->lineChecker->isComment('.. Test'));
        self::assertTrue($this->lineChecker->isComment('..'));
        self::assertTrue($this->lineChecker->isComment('.. with _ underscore'));
        self::assertTrue($this->lineChecker->isComment('.. with : colon'));
        self::assertTrue($this->lineChecker->isComment('.. can finish with colon:'));

        self::assertFalse($this->lineChecker->isComment('Test'));
        self::assertFalse($this->lineChecker->isComment('.. _should not start with underscore'));
        self::assertFalse($this->lineChecker->isComment('.. should not finish with double colon::'));
        self::assertFalse($this->lineChecker->isComment('.. should contain::double colon'));
    }

    public function testIsDirective(): void
    {
        self::assertTrue($this->lineChecker->isDirective('.. code-block::'));
        self::assertTrue($this->lineChecker->isDirective('.. code-block:: php'));
        self::assertTrue($this->lineChecker->isDirective('.. code&block:: php'));
        self::assertTrue($this->lineChecker->isDirective('.. `code-block`:: php'));
        self::assertFalse($this->lineChecker->isDirective('.. code block:: php'));
        self::assertFalse($this->lineChecker->isDirective('.. code block :: php'));
        self::assertFalse($this->lineChecker->isDirective('..code block:: php'));
        self::assertFalse($this->lineChecker->isDirective('.. code-block::php'));
        self::assertFalse($this->lineChecker->isDirective('Test'));
    }

    public function testIsIndented(): void
    {
        self::assertTrue($this->lineChecker->isIndented('    Test'));
        self::assertTrue($this->lineChecker->isIndented('  Test'));
        self::assertFalse($this->lineChecker->isIndented('Test'));
        self::assertFalse($this->lineChecker->isIndented(''));
        self::assertFalse($this->lineChecker->isIndented('  Test', 4));
    }

    public function testIsDefinitionListEnded(): void
    {
        self::assertTrue($this->lineChecker->isDefinitionListEnded('Test', ''));
        self::assertFalse($this->lineChecker->isDefinitionListEnded('Term', '    Definition'));
        self::assertFalse($this->lineChecker->isDefinitionListEnded('', '    Definition'));
    }
}
