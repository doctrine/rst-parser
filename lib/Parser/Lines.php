<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Iterator;

/**
 * @template-implements Iterator<array-key, string>
 */
final class Lines implements Iterator
{
    /** @var string[] */
    private $lines = [];

    /** @var int */
    private $position = 0;

    /**
     * @param string[] $lines
     */
    public function __construct(array $lines)
    {
        $this->lines = $lines;
    }

    public function getPreviousLine(): string
    {
        return $this->lines[$this->position - 1] ?? '';
    }

    public function getNextLine(): string
    {
        return $this->lines[$this->position + 1] ?? '';
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function current(): string
    {
        return $this->lines[$this->position];
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function valid(): bool
    {
        return isset($this->lines[$this->position]);
    }
}
