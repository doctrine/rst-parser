<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Exception;

class ErrorManager
{
    /** @var Configuration */
    private $configuration;

    /** @var string[] */
    private $errors = [];

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function error(string $message, \Throwable $throwable = null): void
    {
        $this->errors[] = $message;

        if ($this->configuration->isAbortOnError()) {
            // throw the actual throwable if one is available
            if ($throwable) {
                throw $throwable;
            }

            throw new \Exception($message);
        }

        if ($this->configuration->isSilentOnError()) {
            return;
        }

        echo '/!\\ ' . $message . "\n";
    }

    /**
     * @return string[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
