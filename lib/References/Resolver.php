<?php

declare(strict_types=1);

namespace Doctrine\RST\References;

use Doctrine\RST\Environment;
use Doctrine\RST\MetaEntry;

class Resolver
{
    public function resolve(Environment $environment, string $data) : ResolvedReference
    {
        $resolvedFileReference = $this->resolveFileReference($environment, $data);

        if ($resolvedFileReference !== null) {
            return $resolvedFileReference;
        }

        $resolvedAnchorReference = $this->resolveAnchorReference($environment, $data);

        if ($resolvedAnchorReference !== null) {
            return $resolvedAnchorReference;
        }

        return new ResolvedReference(
            '(unresolved)',
            '#' . $data
        );
    }

    private function resolveFileReference(Environment $environment, string $data) : ?ResolvedReference
    {
        $entry = null;

        $file = $environment->canonicalUrl($data);

        if ($file !== null) {
            $entry = $environment->getMetas()->get($file);
        }

        if ($entry === null) {
            return null;
        }

        return $this->createResolvedReference($environment, $entry);
    }

    private function resolveAnchorReference(Environment $environment, string $data) : ?ResolvedReference
    {
        $entry = $environment->getMetas()->findLinkMetaEntry($data);

        if ($entry !== null) {
            return $this->createResolvedReference($environment, $entry, $data);
        }

        return null;
    }

    private function createResolvedReference(
        Environment $environment,
        MetaEntry $entry,
        ?string $anchor = null
    ) : ResolvedReference {
        $url = $entry->getUrl();

        if ($url !== '') {
            $url = $environment->relativeUrl('/' . $url) . ($anchor !== null ? '#' . $anchor : '');
        }

        return new ResolvedReference(
            $entry->getTitle(),
            $url,
            $entry->getTitles()
        );
    }
}
