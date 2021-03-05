<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use PHPUnit\Framework\TestCase;

use function strpos;

class ConfigurationTest extends TestCase
{
    /** @var Configuration */
    private $configuration;

    public function testBaseUrl(): void
    {
        self::assertSame('', $this->configuration->getBaseUrl());

        $this->configuration->setBaseUrl('https://www.domain.com/directory');

        self::assertSame('https://www.domain.com/directory', $this->configuration->getBaseUrl());
    }

    public function testBaseUrlEnabledCallable(): void
    {
        $callable = $this->configuration->getBaseUrlEnabledCallable();

        self::assertNull($callable);

        $callable = static function (string $path): bool {
            return strpos($path, 'use-base-url') !== false;
        };

        $this->configuration->setBaseUrlEnabledCallable($callable);

        self::assertSame($callable, $this->configuration->getBaseUrlEnabledCallable());
    }

    public function testIsBaseUrlEnabled(): void
    {
        self::assertFalse($this->configuration->isBaseUrlEnabled('/path'));

        $callable = static function (string $path): bool {
            return strpos($path, '/use-base-url') !== false;
        };

        $this->configuration->setBaseUrl('https://www.domain.com/directory');
        $this->configuration->setBaseUrlEnabledCallable($callable);

        self::assertTrue($this->configuration->isBaseUrlEnabled('/path/use-base-url'));
        self::assertFalse($this->configuration->isBaseUrlEnabled('/path/do-not-use-base-url'));
    }

    public function testAbortOnError(): void
    {
        self::assertTrue($this->configuration->isAbortOnError());

        $this->configuration->abortOnError(false);

        self::assertFalse($this->configuration->isAbortOnError());
    }

    public function testIgnoreInvalidReferences(): void
    {
        self::assertFalse($this->configuration->getIgnoreInvalidReferences());

        $this->configuration->setIgnoreInvalidReferences(true);

        self::assertTrue($this->configuration->getIgnoreInvalidReferences());
    }

    public function testInitialHeaderLevel() : void
    {
        self::assertSame(1, $this->configuration->getInitialHeaderLevel());

        $this->configuration->setInitialHeaderLevel(2);

        self::assertSame(2, $this->configuration->getInitialHeaderLevel());
    }

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
    }
}
