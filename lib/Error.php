<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Throwable;

use function sprintf;

final class Error
{
    /** @var string */
    private $message;

    /** @var string|null */
    private $file;

    /** @var int|null */
    private $line;

    /** @var Throwable|null */
    private $throwable;

    public function __construct(string $message, ?string $file = null, ?int $line = null, ?Throwable $throwable = null)
    {
        $this->message   = $message;
        $this->file      = $file;
        $this->line      = $line;
        $this->throwable = $throwable;
    }

    public function asString(): string
    {
        $output = $this->message;
        if ($this->getFile() !== null) {
            $output .= sprintf(' in file "%s"', $this->file);

            if ($this->line !== null) {
                $output .= sprintf(' at line %d', $this->line);
            }
        }

        return $output;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getFile(): ?string
    {
        if ($this->file === '') {
            return null;
        }

        return $this->file;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }
}
