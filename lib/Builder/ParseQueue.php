<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use function array_shift;

class ParseQueue
{
    /** @var Documents */
    private $documents;

    /** @var string[] */
    private $parseQueue = [];

    /** @var int[] */
    private $states = [];

    public function __construct(Documents $documents)
    {
        $this->documents = $documents;
    }

    public function getState(string $file) : ?int
    {
        return $this->states[$file] ?? null;
    }

    public function setState(string $file, int $state) : void
    {
        $this->states[$file] = $state;
    }

    public function getFileToParse() : ?string
    {
        if ($this->parseQueue !== []) {
            return array_shift($this->parseQueue);
        }

        return null;
    }

    public function addToParseQueue(string $file) : void
    {
        $this->states[$file] = State::PARSE;

        if ($this->documents->hasDocument($file)) {
            return;
        }

        $this->parseQueue[$file] = $file;
    }
}
