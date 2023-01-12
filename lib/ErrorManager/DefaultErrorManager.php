<?php

declare(strict_types=1);

namespace Doctrine\RST\ErrorManager;

use Doctrine\RST\Configuration;
use Doctrine\RST\Error;
use Doctrine\RST\ErrorManager;
use Exception;
use Throwable;

use function sprintf;

final class DefaultErrorManager implements ErrorManager
{
    /** @var Configuration */
    private $configuration;

    /** @var list<Error> */
    private $errors = [];

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    private function log(string $level, string $message, ?string $file = null, ?int $line = null, ?Throwable $throwable = null): void
    {
        $this->errors[] = $error = new Error($level, $message, $file, $line, $throwable);
        if (! $this->configuration->isSilentOnError()) {
            if ($level === Error::LEVEL_WARNING && $this->configuration->isWarningsAsError()) {
                $level = Error::LEVEL_ERROR;
            }

            if ($this->configuration->getOutputFormat() === Configuration::OUTPUT_FORMAT_GITHUB) {
                $file = $error->getFile();
                echo sprintf(
                    '::%s %s%s::%s',
                    $level,
                    $file !== null ? 'file=' . $file : '',
                    $file !== null && $error->getLine() !== null ? ',linefile=' . $error->getLine() : '',
                    $error->getMessage()
                );
            } else {
                echo ($level === Error::LEVEL_ERROR ? '⚡️ ' : '⚠️ ') . $error->asString() . "\n";
            }
        }

        if ($level === Error::LEVEL_ERROR && $this->configuration->isAbortOnError()) {
            throw new Exception($error->asString(), 0, $error->getThrowable());
        }
    }

    public function error(string $message, ?string $file = null, ?int $line = null, ?Throwable $throwable = null): void
    {
        $this->log(Error::LEVEL_ERROR, $message, $file, $line, $throwable);
    }

    public function warning(string $message, ?string $file = null, ?int $line = null, ?Throwable $throwable = null): void
    {
        $this->log(Error::LEVEL_WARNING, $message, $file, $line, $throwable);
    }

    /** @return list<Error> */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
