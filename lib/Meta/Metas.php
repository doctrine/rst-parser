<?php

declare(strict_types=1);

namespace Doctrine\RST\Meta;

use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\UrlGenerator;

use function array_key_exists;
use function array_merge;
use function serialize;
use function strtolower;
use function trim;
use function unserialize;

class Metas
{
    /** @var MetaEntry[] */
    private $entries = [];

    /** @var string[] */
    private $parents = [];

    /** @var array<string, LinkTarget> */
    private array $linkTargets = [];

    private Configuration $configuration;
    private ErrorManager $errorManager;

    /**
     * @param MetaEntry[]               $entries
     * @param array<string, LinkTarget> $linkTargets
     */
    public function __construct(Configuration $configuration, array $entries = [], array $linkTargets = [])
    {
        $this->configuration = $configuration;
        $this->errorManager  = $this->configuration->getErrorManager();
        $this->entries       = $entries;
        $this->linkTargets   = $linkTargets;
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

    /** @return MetaEntry[] */
    public function getAll(): array
    {
        return $this->entries;
    }

    /**
     * @param string[][]                $titles
     * @param mixed[][]                 $tocs
     * @param string[]                  $depends
     * @param array<string, LinkTarget> $linkTargets
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

        $this->linkTargets = array_merge($this->linkTargets, $linkTargets);
    }

    public function get(string $url): ?MetaEntry
    {
        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        }

        return null;
    }

    /** @param MetaEntry[] $metaEntries */
    public function setMetaEntries(array $metaEntries): void
    {
        $this->entries = $metaEntries;
    }

    /** @param array<string, LinkTarget> $linkTargets */
    private function doesLinkTargetExist(array $linkTargets, string $target): bool
    {
        foreach ($linkTargets as $name => $linkTarget) {
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

    /** @return array<string, LinkTarget> */
    public function getLinkTargets(): array
    {
        return $this->linkTargets;
    }

    public function setLinkTarget(string $name, LinkTarget $linkTarget): void
    {
        if (array_key_exists($name, $this->linkTargets)) {
            $this->errorManager->warning('Duplicate anchor ".. _' . $linkTarget->getName() . '" found.');
            $i = 2;
            while (array_key_exists($name . '-' . $i, $this->linkTargets)) {
                $i++;
            }

            $name .= '-' . $i;
            $linkTarget->setName($name);
            $linkTarget->setDuplicate(true);
        }

        $this->linkTargets[$name] = $linkTarget;
    }

    public function getLinkTarget(UrlGenerator $urlGenerator, string $currentFileName, string $name, bool $relative = true): ?LinkTarget
    {
        $name = trim(strtolower($name));

        if (isset($this->linkTargets[$name])) {
            $link = $this->linkTargets[$name];

            if ($relative) {
                return $this->makeLinkTargetRelative($urlGenerator, $currentFileName, $link);
            }

            return $link;
        }

        return null;
    }

    public function makeLinkTargetRelative(UrlGenerator $urlGenerator, string $currentFileName, LinkTarget $linkTarget): LinkTarget
    {
        $url = $urlGenerator->relativeUrl($linkTarget->getUrl(), $currentFileName);
        $linkTarget->setUrl($url);

        return $linkTarget;
    }

    public function hasLinkTarget(string $name): bool
    {
        return array_key_exists($name, $this->linkTargets);
    }

    public function serialize(): string
    {
        return serialize([
            'entries' => $this->entries,
            'linkTargets' => $this->linkTargets,
        ]);
    }

    public function unserialize(string $serializedData): void
    {
        $data              = unserialize($serializedData);
        $this->entries     = $data['entries'] ?? [];
        $this->linkTargets = $data['linkTargets'] ?? [];
    }
}
