<?php

declare(strict_types=1);

namespace Doctrine\RST\Meta;

use Doctrine\RST\Environment;

use function strtolower;

class Metas
{
    /** @var MetaEntry[] */
    private $entries = [];

    /** @var string[] */
    private $parents = [];

    /**
     * @param MetaEntry[] $entries
     */
    public function __construct(array $entries = [])
    {
        $this->entries = $entries;
    }

    public function findLinkTargetMetaEntry(string $linkTarget): ?MetaEntry
    {
        foreach ($this->entries as $entry) {
            if ($this->doesLinkTargetExist($entry->getLinkTargets(), $linkTarget)) {
                return $entry;
            }
        }

        return $this->findByTitle($linkTarget);
    }

    /**
     * @return MetaEntry[]
     */
    public function getAll(): array
    {
        return $this->entries;
    }

    /**
     * @param string[][] $titles
     * @param mixed[][]  $tocs
     * @param string[]   $depends
     * @param string[]   $linkTargets
     */
    public function set(
        string $file,
        string $url,
        string $title,
        array $titles,
        array $tocs,
        int $mtime,
        array $depends,
        array $linkTargets
    ): void {
        foreach ($tocs as $toc) {
            foreach ($toc as $child) {
                $this->parents[$child] = $file;

                if (! isset($this->entries[$child])) {
                    continue;
                }

                $this->entries[$child]->setParent($file);
            }
        }

        $this->entries[$file] = new MetaEntry(
            $file,
            $url,
            $title,
            $titles,
            $tocs,
            $depends,
            $linkTargets,
            $mtime
        );

        if (! isset($this->parents[$file])) {
            return;
        }

        $this->entries[$file]->setParent($this->parents[$file]);
    }

    public function get(string $url): ?MetaEntry
    {
        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        }

        return null;
    }

    /**
     * @param MetaEntry[] $metaEntries
     */
    public function setMetaEntries(array $metaEntries): void
    {
        $this->entries = $metaEntries;
    }

    /**
     * @param string[] $linkTargets
     */
    private function doesLinkTargetExist(array $linkTargets, string $target): bool
    {
        foreach ($linkTargets as $name => $url) {
            if ($name === strtolower($target)) {
                return true;
            }
        }

        return false;
    }

    private function findByTitle(string $text): ?MetaEntry
    {
        $text = Environment::slugify($text);

        // try to lookup the document reference by title
        foreach ($this->entries as $entry) {
            if ($entry->hasTitle($text)) {
                return $entry;
            }
        }

        return null;
    }
}
