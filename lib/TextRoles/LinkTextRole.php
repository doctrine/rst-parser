<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\InvalidLink;
use Doctrine\RST\Span\SpanToken;

class LinkTextRole extends BaseTextRole
{
    public function getName(): string
    {
        return SpanToken::TYPE_LINK;
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $url      = $spanToken->get('url');
        $link     = $spanToken->get('link');
        $linktext = $spanToken->get('linktext');

        if ($url === '') {
            $linkTarget = $environment->getLinkTarget($link);
            if ($linkTarget !== null) {
                $url = $linkTarget->getUrl();
            }

            if ($url === '') {
                $metaEntry = $environment->getMetaEntry();

                if ($metaEntry !== null && $metaEntry->hasTitle($link)) {
                    // A strangely-complex way to simply get the current relative URL
                    // For example, if the current page is "reference/page", then
                    // this would return "page" so the final URL is href="page#some-anchor".
                    $currentRelativeUrl = $environment->relativeUrl('/' . $metaEntry->getUrl());
                    $url                = $currentRelativeUrl . '#' . Environment::slugify($link);
                }
            }

            if ($url === '') {
                $environment->addInvalidLink(new InvalidLink($link));

                return $link;
            }
        }

        return $this->link($environment, $url, $linktext);
    }

    /** @param mixed[] $attributes */
    public function link(Environment $environment, ?string $url, string $title, array $attributes = []): string
    {
        return $environment->getLinkRenderer()->renderUrl($url, $title, $attributes);
    }
}
