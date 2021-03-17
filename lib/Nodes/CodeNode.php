<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

/**
 * Represents a "code node", which encompasses more than "code blocks".
 *
 * A code node is any block that's introduced with a :: followed
 * by a number of lines that are considered the "value" of that node.
 *
 * For example, a code-block would be parsed as a CodeNode:
 *
 *      .. code-block:: php
 *
 *          // I am the first line of the value
 *          // I am the second line
 *
 * But a toctree is *also* considered a CodeNode
 *
 *      .. toctree::
 *          :maxdepth: 1
 *
 *          file
 *          file2
 */
class CodeNode extends Node
{
    /** @var string */
    protected $value;

    /** @var bool */
    protected $raw = false;

    /** @var string|null */
    protected $language = null;

    /** @var string[] */
    private $options = [];

    /**
     * @param string[] $lines
     */
    public function __construct(array $lines)
    {
        parent::__construct($this->normalizeLines($lines));
    }

    /**
     * The "contents" of the block - see the class description for more details.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function setLanguage(?string $language = null): void
    {
        $this->language = $language;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setRaw(bool $raw): void
    {
        $this->raw = $raw;
    }

    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * @param string[] $options
     */
    public function setOptions(array $options = []): void
    {
        $this->options = $options;
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
