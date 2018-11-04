<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /** @var Configuration */
    private $configuration;

    public function testBaseUrl() : void
    {
        self::assertSame('', $this->configuration->getBaseUrl());

        $this->configuration->setBaseUrl('https://www.domain.com/directory');

        self::assertSame('https://www.domain.com/directory', $this->configuration->getBaseUrl());
    }

    public function testAbortOnError() : void
    {
        self::assertTrue($this->configuration->isAbortOnError());

        $this->configuration->abortOnError(false);

        self::assertFalse($this->configuration->isAbortOnError());
    }

    protected function setUp() : void
    {
        $this->configuration = new Configuration();
    }
}
