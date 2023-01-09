<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Throwable;

interface ErrorManager
{
    public function error(
        string $message,
        ?string $file = null,
        ?int $line = null,
        ?Throwable $throwable = null
    ): void;

    public function warning(
        string $message,
        ?string $file = null,
        ?int $line = null,
        ?Throwable $throwable = null
    ): void;

    /** @return list<Error> */
    public function getErrors(): array;
}
