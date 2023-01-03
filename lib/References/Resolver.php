<?php

declare(strict_types=1);

namespace Doctrine\RST\References;

use Doctrine\RST\Environment;
use Doctrine\RST\Event\MissingReferenceResolverEvent;
use Doctrine\RST\Meta\MetaEntry;

final class Resolver
{
    /** @param string[] $attributes */
    public function resolve(
        Environment $environment,
        string $data,
        array $attributes = []
    ): ?ResolvedReference {
        $eventManager = $environment->getConfiguration()->getEventManager();

        $resolvedFileReference = $this->resolveFileReference($environment, $data, $attributes);

        if ($resolvedFileReference !== null) {
            return $resolvedFileReference;
        }

        $resolvedAnchorReference = $this->resolveAnchorReference($environment, $data, $attributes);

        if ($resolvedAnchorReference !== null) {
            return $resolvedAnchorReference;
        }

        $missingReferenceResolverEvent = new MissingReferenceResolverEvent($environment, $data, $attributes);

        $eventManager->dispatchEvent(
            MissingReferenceResolverEvent::MISSING_REFERENCE_RESOLVER,
            $missingReferenceResolverEvent
        );

        if ($missingReferenceResolverEvent->getResolvedReference() !== null) {
            return $missingReferenceResolverEvent->getResolvedReference();
        }

        return null;
    }

    /** @param string[] $attributes */
    private function resolveFileReference(
        Environment $environment,
        string $data,
        array $attributes = []
    ): ?ResolvedReference {
        $entry = null;

        $file = $environment->canonicalUrl($data);

        if ($file !== null) {
            $entry = $environment->getMetas()->get($file);
        }

        if ($entry === null) {
            return null;
        }

        return $this->createResolvedReference($file, $environment, $entry, null, $attributes);
    }

    /** @param string[] $attributes */
    private function resolveAnchorReference(
        Environment $environment,
        string $data,
        array $attributes = []
    ): ?ResolvedReference {
        $entry = $environment->getMetas()->findLinkTargetMetaEntry($data);
        if ($entry === null) {
            return null;
        }

        $linkTarget = $entry->getLinkTarget($data);
        $title      = null;
        if ($linkTarget !== null) {
            $title = $linkTarget->getTitle();
        }

        return $this->createResolvedReference($entry->getFile(), $environment, $entry, $title, $attributes, $data);
    }

    /** @param string[] $attributes */
    private function createResolvedReference(
        ?string $file,
        Environment $environment,
        MetaEntry $entry,
        ?string $title = null,
        array $attributes = [],
        ?string $anchor = null
    ): ResolvedReference {
        $url = $entry->getUrl();

        if ($url !== '') {
            $url = $environment->relativeUrl('/' . $url) . ($anchor !== null ? '#' . $anchor : '');
        }

        return new ResolvedReference(
            $file,
            $title ?? $entry->getTitle(),
            $url,
            $entry->getTitles(),
            $attributes
        );
    }
}
