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

    public function testProcessingLinkSpanSetsLinkTarget(): void
    {
        $this->environment->expects(self::exactly(1))->method('setLinkTarget');
        $spanProcessor = new SpanProcessor($this->environment, '`example <http://example.org/>`_');
        $spanProcessor->process();
    }

    public function testProcessingAnonymousLinkSpanDoesNotSetLinkTarget(): void
    {
        $this->environment->expects(self::never())->method('setLinkTarget');
        $spanProcessor = new SpanProcessor($this->environment, '`example <http://example.org/>`__');
        $spanProcessor->process();
    }
}
