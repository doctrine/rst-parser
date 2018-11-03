<?php

declare(strict_types=1);

namespace Doctrine\RST;

class Configuration
{
    /** @var string */
    private $baseUrl = '';

    /** @var bool */
    private $abortOnError = true;

    public function getBaseUrl() : string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl) : self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function isAbortOnError() : bool
    {
        return $this->abortOnError;
    }

    public function abortOnError(bool $abortOnError) : void
    {
        $this->abortOnError = $abortOnError;
    }
}
