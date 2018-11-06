<?php

declare(strict_types=1);

namespace Doctrine\RST;

class Configuration
{
    /** @var string */
    private $baseUrl = '';

    /** @var callable|null */
    private $baseUrlEnabledCallable;

    /** @var bool */
    private $abortOnError = true;

    /** @var bool */
    private $ignoreInvalidReferences = false;

    public function getBaseUrl() : string
    {
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl) : self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function setBaseUrlEnabledCallable(?callable $baseUrlEnabledCallable) : void
    {
        $this->baseUrlEnabledCallable = $baseUrlEnabledCallable;
    }

    public function getBaseUrlEnabledCallable() : ?callable
    {
        return $this->baseUrlEnabledCallable;
    }

    public function isBaseUrlEnabled(string $path) : bool
    {
        if ($this->baseUrl === '') {
            return false;
        }

        if ($this->baseUrlEnabledCallable !== null) {
            /** @var callable $baseUrlEnabledCallable */
            $baseUrlEnabledCallable = $this->baseUrlEnabledCallable;

            return $baseUrlEnabledCallable($path);
        }

        return true;
    }

    public function isAbortOnError() : bool
    {
        return $this->abortOnError;
    }

    public function abortOnError(bool $abortOnError) : void
    {
        $this->abortOnError = $abortOnError;
    }

    public function getIgnoreInvalidReferences() : bool
    {
        return $this->ignoreInvalidReferences;
    }

    public function setIgnoreInvalidReferences(bool $ignoreInvalidReferences) : void
    {
        $this->ignoreInvalidReferences = $ignoreInvalidReferences;
    }
}
