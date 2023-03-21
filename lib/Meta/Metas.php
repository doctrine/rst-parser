<?php

declare(strict_types=1);

namespace Doctrine\RST\Meta;

use Doctrine\RST\Configuration;
use Doctrine\RST\Environment;
use Doctrine\RST\ErrorManager;
use Doctrine\RST\UrlGenerator;

use function array_key_exists;
use function array_merge;
use function in_array;
use function serialize;
use function sprintf;
use function str_starts_with;
use function strtolower;
use function substr;
use function trim;
use function unserialize;

class Metas
{
    /** @var DocumentMetaData[] */
    private $entries = [];

    /** @var string[] */
    private array $parents = [];

    /** @var array<string, LinkTarget> */
    private array $linkTargets = [];

    private Configuration $configuration;
    private ErrorManager $errorManager;

    private ?DocumentMetaData $documentRoot = null;

    /**
     * @param DocumentMetaData[]        $entries
     * @param array<string, LinkTarget> $linkTargets
     */
    public function __construct(Configuration $configuration, array $entries = [], array $linkTargets = [])
    {
        $this->configuration = $configuration;
        $this->errorManager  = $this->configuration->getErrorManager();
        $this->entries       = $entries;
        $this->linkTargets   = $linkTargets;
    }

    public function findLinkTargetMetaEntry(string $linkTarget): ?DocumentMetaData
    {
        foreach ($this->entries as $entry) {
            if ($this->doesLinkTargetExist($entry->getLinkTargets(), $linkTarget)) {
                return $entry;
            }
        }

        return $this->findByTitle($linkTarget);
    }

    /** @return DocumentMetaData[] */
    public function getAll(): array
    {
        return $this->entries;
    }

    public function set(DocumentMetaData $documentMetaData): void
    {
        foreach ($documentMetaData->getTocs() as $toc) {
            foreach ($toc as $child) {
                $this->parents[$child] = $documentMetaData->getFile();

                if (! isset($this->entries[$child])) {
                    continue;
                }

                $this->entries[$child]->setParent($documentMetaData->getFile());
            }
        }

        $this->entries[$documentMetaData->getFile()] = $documentMetaData;

        if (! isset($this->parents[$documentMetaData->getFile()])) {
            return;
        }

        $this->entries[$documentMetaData->getFile()]->setParent($this->parents[$documentMetaData->getFile()]);

        $this->linkTargets = array_merge($this->linkTargets, $documentMetaData->getLinkTargets());
    }

    public function get(string $url): ?DocumentMetaData
    {
        if (str_starts_with($url, '/')) {
            $url = substr($url, 1);
        }

        if (isset($this->entries[$url])) {
            return $this->entries[$url];
        }

        return null;
    }

    /** @param DocumentMetaData[] $metaEntries */
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

    private function findByTitle(string $text): ?DocumentMetaData
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

    public function buildTocTree(string $indexFilename): void
    {
        if (! isset($this->entries[$indexFilename])) {
            $this->errorManager->error('No document root was found, expected: ' . $indexFilename);

            return;
        }

        $this->documentRoot = $this->entries[$indexFilename];
        $this->documentRoot->setDocumentRoot(true);
        $this->buildSubTree($this->documentRoot);
    }

    private function buildSubTree(?DocumentMetaData $document): void
    {
        $tocList = $document->getTocs();
        foreach ($tocList as $toc) {
            foreach ($toc as $tocEntry) {
                if (! isset($this->entries[$tocEntry])) {
                    $this->errorManager->warning(sprintf('File %s not found in document meta data.', $tocEntry));
                    continue;
                }

                if ($this->entries[$tocEntry] === $document) {
                    // Do not add document as its own child
                    continue;
                }

                if ($this->entries[$tocEntry]->getParentDocument() !== null) {
                    if ($document->getFile() !== $this->entries[$tocEntry]->getParentDocument()->getFile()) {
                        $this->errorManager->warning(
                            sprintf(
                                'Document "%s" was added to document tree several times. Parents found: "%s" and "%s".',
                                $tocEntry,
                                $document->getFile(),
                                $this->entries[$tocEntry]->getParentDocument()->getFile()
                            )
                        );
                    }

                    continue;
                }

                $this->entries[$tocEntry]->setParentDocument($document);
                if (in_array($this->entries[$tocEntry], $document->getChildDocuments(), true)) {
                    continue;
                }

                $document->addChildDocument($this->entries[$tocEntry]);
                $this->buildSubTree($this->entries[$tocEntry]);
            }
        }
    }

    public function getDocumentRoot(): ?DocumentMetaData
    {
        return $this->documentRoot;
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
