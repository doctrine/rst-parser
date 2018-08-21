<?php

declare(strict_types=1);

namespace Doctrine\RST;

class Metas
{
    /** @var mixed[] */
    protected $entries = [];

    /** @var string[] */
    protected $parents = [];

    /**
     * @param mixed[]|null $entries
     */
    public function __construct(?array $entries)
    {
        if ($entries === null) {
            return;
        }

        $this->entries = $entries;
    }

    /**
     * @return mixed[]
     */
    public function getAll() : array
    {
        return $this->entries;
    }

    /**
     * @param string[][] $titles
     * @param mixed[][]  $tocs
     * @param string[]   $depends
     */
    public function set(
        string $file,
        string $url,
        ?string $title,
        array $titles,
        array $tocs,
        int $ctime,
        array $depends
    ) : void {
        foreach ($tocs as $toc) {
            foreach ($toc as $child) {
                $this->parents[$child] = $file;
                if (! isset($this->entries[$child])) {
                    continue;
                }

                $this->entries[$child]['parent'] = $file;
            }
        }

        $this->entries[$file] = [
            'file' => $file,
            'url' => $url,
            'title' => $title,
            'titles' => $titles,
            'tocs' => $tocs,
            'ctime' => $ctime,
            'depends' => $depends,
        ];

        if (! isset($this->parents[$file])) {
            return;
        }

        $this->entries[$file]['parent'] = $this->parents[$file];
    }

    /**
     * @return string[]
     */
    public function get(string $url) : ?array
    {
        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        }

        return null;
    }
}
