<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpanProcessorTest extends TestCase
{
    /** @var Environment|MockObject */
    private $environment;

    protected function setUp(): void
    {
        $this->environment = $this->createMock(Environment::class);
    }

    public function testGenerateIdIsUnique(): void
    {
        $spanProcessor = new SpanProcessor($this->environment, '`example <http://example.org/>`__');
        self::assertNotEmpty($spanProcessor->generateId());
        self::assertNotEquals($spanProcessor->generateId(), $spanProcessor->generateId());
    }

    public function testGenerateIdFromDifferentSpanProcessors(): void
    {
        $spanProcessor  = new SpanProcessor($this->environment, '`example <http://example.org/>`__');
        $spanProcessor2 = new SpanProcessor($this->environment, '`example <http://example.org/>`__');
        self::assertNotEquals($spanProcessor->generateId(), $spanProcessor2->generateId());
    }
}
