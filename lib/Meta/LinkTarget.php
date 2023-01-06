<?php

declare(strict_types=1);

namespace Doctrine\RST\Meta;

use function strtolower;
use function trim;

class LinkTarget
{
    private string $name;
    private string $url;
    private ?string $title;
    private bool $anonymous = false;
    private bool $duplicate = false;

    public function __construct(
        string $name,
        string $url,
        ?string $title = null
    ) {
        $this->name      = trim(strtolower($name));
        $this->url       = trim($url);
        $this->title     = $title;
        $this->anonymous = $name === '_';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isDuplicate(): bool
    {
        return $this->duplicate;
    }

    public function setDuplicate(bool $duplicate): void
    {
        $this->duplicate = $duplicate;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function isAnonymous(): bool
    {
        return $this->anonymous;
    }
}
