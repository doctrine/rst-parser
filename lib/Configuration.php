<?php

declare(strict_types=1);

namespace Doctrine\RST;

class Configuration
{
    /** @var bool */
    private $useRelativeUrls = true;

    /** @var bool */
    private $abortOnError = true;

    public function useRelativeUrls() : bool
    {
        return $this->useRelativeUrls;
    }

    public function setUseRelativeUrls(bool $useRelativeUrls) : self
    {
        $this->useRelativeUrls = $useRelativeUrls;

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
