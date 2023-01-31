<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Throwable;

use function sprintf;

final class Error
{
    public const LEVEL_ERROR   = 'error';
    public const LEVEL_WARNING = 'warning';

    private string $level;

    private string $message;

    private ?string $file = null;

    private ?int $line = null;

    private ?Throwable $throwable = null;

    public function __construct(string $level, string $message, ?string $file = null, ?int $line = null, ?Throwable $throwable = null)
    {
        $this->level     = $level;
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

    public function getLevel(): string
    {
        return $this->level;
    }

    public function setLevel(string $level): void
    {
        $this->level = $level;
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
