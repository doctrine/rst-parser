<?php

declare(strict_types=1);

namespace Doctrine\RST\Utility;

use Doctrine\RST\Environment;
use Doctrine\RST\References\Resolver;

use function count;
use function is_array;
use function is_countable;

final class TitleLinkUtility
{
    private Environment $environment;
    private int $maxDepth;

    public function __construct(Environment $environment, int $maxDepth)
    {
        $this->environment = $environment;
        $this->maxDepth    = $maxDepth;
    }

    /**
     * @param mixed[]|array $titles
     * @param mixed[]       $tocItems
     */
    public function buildLevel(
        ?string $url,
        array $titles,
        int $level,
        array &$tocItems,
        string $file
    ): void {
        foreach ($titles as $k => $entry) {
            [$title, $children] = $entry;

            [$title, $target] = $this->generateTarget(
                $url,
                $title,
                // don't add anchor for first h1 in a different file (link directly to the file)
                ! ($level === 1 && $k === 0 && $file !== '/' . $this->environment->getCurrentFileName())
            );

            $tocItem = [
                'targetId' => $this->generateTargetId($target),
                'targetUrl' => $this->environment->generateUrl($target),
                'title' => $title,
                'level' => $level,
                'children' => [],
                'subpages' => [],
            ];

            // render children until we hit the configured maxdepth
            if ((is_countable($children) ? count($children) : 0) > 0 && $level < $this->maxDepth) {
                $this->buildLevel($url, $children, $level + 1, $tocItem['children'], $file);
            }

            $tocItems[] = $tocItem;
        }
    }

    private function generateTargetId(string $target): string
    {
        return Environment::slugify($target);
    }

    /**
     * @param string[]|string $title
     *
     * @return mixed[]
     */
    private function generateTarget(?string $url, $title, bool $withAnchor): array
    {
        $target   = $url;
        $resolver = new Resolver();
        if ($withAnchor) {
            $anchor  = $this->generateAnchorFromTitle($title);
            $target .= '#' . $anchor;
        }

        if (is_array($title)) {
            [$title, $target] = $title;

            $reference = $resolver->resolve($this->environment, 'doc', $target);

            if ($reference === null) {
                return [$title, $target];
            }

            $target = $this->environment->relativeUrl($reference->getUrl());
        }

        return [$title, $target];
    }

    /** @param string[]|string $title */
    private function generateAnchorFromTitle($title): string
    {
        $slug = is_array($title)
            ? $title[1]
            : $title;

        return Environment::slugify($slug);
    }
}
