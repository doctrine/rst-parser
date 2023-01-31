<?php

declare(strict_types=1);

namespace Doctrine\RST\References;

use RuntimeException;

use function is_string;
use function preg_match;
use function sprintf;

final class ResolvedReference
{
    private ?string $file = null;

    private ?string $title = null;

    private ?string $url = null;

    /** @var string[][]|string[][][] */
    private array $titles;

    /** @var string[] */
    private array $attributes;

    /**
     * @param string[][]|string[][][] $titles
     * @param string[]                $attributes
     */
    public function __construct(?string $file, ?string $title, ?string $url, array $titles = [], array $attributes = [])
    {
        $this->file   = $file;
        $this->title  = $title;
        $this->url    = $url;
        $this->titles = $titles;

        $this->validateAttributes($attributes);
        $this->attributes = $attributes;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /** @return string[][]|string[][][] */
    public function getTitles(): array
    {
        return $this->titles;
    }

    /** @return string[] */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @param string[] $attributes */
    private function validateAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute => $value) {
            if (! is_string($attribute) || $attribute === 'href' || ! (bool) preg_match('/^[a-zA-Z\_][\w\.\-_]+$/', $attribute)) {
                throw new RuntimeException(sprintf('Attribute with name "%s" is not allowed', $attribute));
            }
        }
    }
}
