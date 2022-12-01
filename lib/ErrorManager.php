<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Exception;
use Throwable;

use function sprintf;

class ErrorManager
{
    /** @var Configuration */
    private $configuration;

    /** @var list<Error> */
    private $errors = [];

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function error(string $message, ?string $file = null, ?int $line = null, ?Throwable $throwable = null): void
    {
        $this->errors[] = $error = new Error($message, $file, $line, $throwable);

        if (! $this->configuration->isSilentOnError()) {
            if ($this->configuration->getOutputFormat() === Configuration::OUTPUT_FORMAT_GITHUB) {
                $file = $error->getFile();
                echo sprintf(
                    '::error %s%s::%s',
                    $file !== null ? 'file=' . $file : '',
                    $file !== null && $error->getLine() !== null ? ',linefile=' . $error->getLine() : '',
                    $error->getMessage()
                );
            } else {
                echo '⚠️ ' . $error->asString() . "\n";
            }
        }

        if ($this->configuration->isAbortOnError()) {
            throw new Exception($error->asString(), 0, $error->getThrowable());
        }
    }

    public function warning(string $message, ?string $file = null, ?int $line = null, ?Throwable $throwable = null): void
    {
        if ($this->configuration->isWarningsAsError()) {
            $this->error($message, $file, $line, $throwable);

            return;
        }

        if ($this->configuration->isSilentOnError()) {
            return;
        }

        $error = new Error($message, $file, $line, $throwable);
        if ($this->configuration->getOutputFormat() === Configuration::OUTPUT_FORMAT_GITHUB) {
            $file = $error->getFile();
            echo sprintf(
                '::warning %s%s::%s',
                $file !== null ? 'file=' . $file : '',
                $file !== null && $error->getLine() !== null ? ',linefile=' . $error->getLine() : '',
                $error->getMessage()
            );
        } else {
            echo $error->asString() . "\n";
        }
    }

    /** @return list<Error> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
