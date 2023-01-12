<?php

declare(strict_types=1);

namespace Doctrine\RST\ErrorManager;

use Doctrine\RST\Configuration;
use Doctrine\RST\ErrorManager;

final class DefaultErrorManagerFactory implements ErrorManagerFactory
{
    private ?ErrorManager $errorManager = null;
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getErrorManager(): ErrorManager
    {
        if ($this->errorManager === null) {
            $this->errorManager = new DefaultErrorManager($this->configuration);
        }

        return $this->errorManager;
    }
}
