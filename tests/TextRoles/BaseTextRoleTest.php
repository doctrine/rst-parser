<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\TextRoles\BaseTextRole;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BaseTextRoleTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;

    protected function setUp(): void
    {
        $this->environment = $this->createMock(Environment::class);
    }

    public function testTextProcessing(): void
    {
        $baseTextRole = $this->getMockForAbstractClass(BaseTextRole::class);
        $name         = 'somerole';
        $baseTextRole->method('getName')->willReturn($name);
        $text         = 'Some text';
        $concreteData = $baseTextRole->process($this->environment, $text);

        self::assertArrayHasKey('text', $concreteData);
        self::assertEquals($text, $concreteData['text']);
        self::assertArrayHasKey('section', $concreteData);
        self::assertEquals($name, $concreteData['section']);
    }
}
