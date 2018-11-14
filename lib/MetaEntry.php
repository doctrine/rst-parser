<?php

declare(strict_types=1);

namespace Doctrine\RST;

use function array_merge;
use function is_array;
use function is_string;

class MetaEntry
{
    /** @var string */
    private $file;

    /** @var string */
    private $url;

    /** @var string */
    private $title;

    /** @var string[][]|string[][][] */
    private $titles;

    /** @var mixed[][] */
    private $tocs;

    /** @var int */
    private $ctime;

    /** @var string[] */
    private $depends;

    /** @var string[] */
    private $links;

    /** @var string|null */
    private $parent;

    /**
     * @param string[][]|string[][][] $titles
     * @param mixed[][]               $tocs
     * @param string[]                $depends
     * @param string[]                $links
     */
    public function __construct(
        string $file,
        string $url,
        string $title,
        array $titles,
        array $tocs,
        array $depends,
        array $links,
        int $ctime
    ) {
        $this->file    = $file;
        $this->url     = $url;
        $this->title   = $title;
        $this->titles  = $titles;
        $this->tocs    = $tocs;
        $this->depends = $depends;
        $this->links   = $links;
        $this->ctime   = $ctime;
    }

    public function getFile() : string
    {
        return $this->file;
    }

    public function getUrl() : string
    {
        return $this->url;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    /**
     * @return string[][]|string[][][]
     */
    public function getTitles() : array
    {
        return $this->titles;
    }

    public function hasTitle(string $text) : bool
    {
        $titles = $this->getAllTitles();

        $text = Environment::slugify($text);

        foreach ($titles as $title) {
            if ($text === Environment::slugify($title)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed[][]
     */
    public function getTocs() : array
    {
        return $this->tocs;
    }

    /**
     * @return string[]
     */
    public function getDepends() : array
    {
        return $this->depends;
    }

    /**
     * @return string[]
     */
    public function getLinks() : array
    {
        return $this->links;
    }

    public function getCtime() : int
    {
        return $this->ctime;
    }

    public function setParent(string $parent) : void
    {
        $this->parent = $parent;
    }

    public function getParent() : ?string
    {
        return $this->parent;
    }

    /**
     * @param string[]|string[][]|null $entryTitles
     *
     * @return string[]
     */
    private function getAllTitles(?array $entryTitles = null) : array
    {
        if ($entryTitles === null) {
            $entryTitles = $this->titles;
        }

        $titles = [];

        foreach ($entryTitles as $title) {
            if (is_string($title[0])) {
                $titles[] = $title[0];
            }

            if (! is_array($title[1])) {
                continue;
            }

            $titles = array_merge($titles, $this->getAllTitles($title[1]));
        }

        return $titles;
    }
}
