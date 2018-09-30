<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST;

use Doctrine\RST\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /** @var Configuration */
    private $configuration;

    public function testUseRelativeUrls() : void
    {
        self::assertTrue($this->configuration->useRelativeUrls());

        $this->configuration->setUseRelativeUrls(false);

        self::assertFalse($this->configuration->useRelativeUrls());
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
