<?php

declare(strict_types=1);

namespace Doctrine\RST\References;

class ResolvedReference
{
    /** @var string|null */
    private $title;

    /** @var string|null */
    private $url;

    /** @var string[][]|string[][][] */
    private $titles;

    /**
     * @param string[][]|string[][][] $titles
     */
    public function __construct(?string $title, ?string $url, array $titles = [])
    {
        $this->title  = $title;
        $this->url    = $url;
        $this->titles = $titles;
    }

    public function getTitle() : ?string
    {
        return $this->title;
    }

    public function getUrl() : ?string
    {
        return $this->url;
    }

    /**
     * @return string[][]|string[][][]
     */
    public function getTitles() : array
    {
        return $this->titles;
    }
}
