<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

/**
 * Represents a "code node", which *sometimes* encompasses more than "code blocks".
 *
 * The intention of this class is for it to be used for "code blocks".
 * However, if a directive returns true from wantCode(), they will
 * be passed a CodeNode.
 */
class CodeNode extends Node
{
    /** @var string */
    protected $value;

    /** @var bool */
    private $raw = false;

    /** @var string|null */
    private $language = null;

    /** @var string[] */
    private $options = [];

    /** @param string[] $lines */
    public function __construct(array $lines)
    {
        parent::__construct($this->normalizeLines($lines));
    }

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

    /** @param string[] $options */
    public function setOptions(array $options = []): void
    {
        $this->options = $options;
    }

    /** @return string[] */
    public function getOptions(): array
    {
        return $this->options;
    }
}
