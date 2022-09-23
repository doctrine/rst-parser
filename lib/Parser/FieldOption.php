<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use function ltrim;
use function str_replace;
use function strlen;
use function trim;

final class FieldOption
{
    /** @var string */
    private $name;

    /** @var int */
    private $offset;

    /** @var string */
    private $body;

    /** @var int */
    private $lineCount = 0;

    public function __construct(string $name, int $offset, string $body)
    {
        $this->name   = str_replace('\: ', ': ', $name);
        $this->offset = $offset + 1;
        $this->body   = $body;
    }

    public function appendLine(string $line): void
    {
        $trimmedLine = ltrim($line);
        if (strlen($trimmedLine) === 0) {
            return;
        }

        if (++$this->lineCount === 1) {
            $this->offset = strlen($line) - strlen($trimmedLine);
        }

        $this->body .= ' ' . $trimmedLine;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return true|string
     */
    public function getBody()
    {
        return trim($this->body) === '' ? true : $this->body;
    }
}
