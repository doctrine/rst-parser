<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\InvalidLink;
use Doctrine\RST\Meta\LinkTarget;
use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanToken;

use function preg_match;
use function preg_match_all;
use function preg_replace;
use function preg_replace_callback;
use function str_replace;
use function substr;

class LinkTextRole extends SpecialTextRole
{
    private ?SpanProcessor $spanProcessor = null;
    private ?Environment $environment     = null;

    public function __construct()
    {
        parent::__construct(SpanToken::TYPE_LINK);
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

    public function replaceAndRegisterTokens(SpanProcessor $spanProcessor, string $span): string
    {
        $this->spanProcessor = $spanProcessor;
        // Signaling anonymous names
        $this->environment = $this->spanProcessor->getEnvironment();
        $this->environment->resetAnonymousStack();

        if (preg_match_all('/(_*)(([a-z0-9]+)|(`(.+)`))__/mUsi', $span, $matches) > 0) {
            foreach ($matches[3] as $k => $y) {
                $name = $matches[3][$k] ? $matches[3][$k] : $matches[5][$k];

                // string prefixed with _ is not an anonymous link
                if ($matches[1][$k]) {
                    continue;
                }

                $this->environment->pushAnonymous($name);
            }
        }

        // Replacing anonymous links  `here <http://google.com>`__
        $span = (string) preg_replace_callback(
            '/(^|[ \(])(([a-z0-9_-]+)|(`(.+)`))__([^a-z0-9]{1}|$)/mUsi',
            [$this, 'registerAnonymousLinkFromMatches'],
            $span
        );

        // Replacing other links  `Google <http://google.com>`_
        $span = (string) preg_replace_callback(
            '/(^|[ \(])(([a-z0-9_-]+)|(`(.+)`))_([^a-z0-9_]{1}|$)/mUsi',
            [$this, 'registerLinkFromMatches'],
            $span
        );

        return $span;
    }

    /** @param string[] $match */
    private function registerAnonymousLinkFromMatches(array $match): string
    {
        return $this->registerLinkFromMatches($match, true);
    }

    /** @param string[] $match */
    private function registerLinkFromMatches(array $match, bool $anonymous = false): string
    {
        $link = $match[3] !== '' ? $match[3] : $match[5];

        // a link starting with _ is not a link - return original string
        if (substr($link, 0, 1) === '_') {
            return $match[0];
        }

        // the link may have a new line in it so we need to strip it
        // before setting the link and adding a token to be replaced
        $link = str_replace("\n", ' ', $link);
        $link = (string) preg_replace('/\s+/', ' ', $link);

        // we need to maintain the characters before and after the link
        $prev = $match[1]; // previous character before the link
        $next = $match[6]; // next character after the link

        $url = '';

        // extract the url if the link was in this format: `test link <https://www.google.com>`_
        if (preg_match('/^(.+)[ \n]<(.+)>$/mUsi', $link, $m) > 0) {
            $link = $m[1];
            $url  = $m[2];
            if (! $anonymous) {
                $this->environment->setLinkTarget(new LinkTarget($link, $url));
            }
        }

        // extract the url if the link was in this format: `<https://www.google.com>`_
        if (preg_match('/^<(.+)>$/mUsi', $link, $m) > 0) {
            $link = $m[1];
            $url  = $m[1];
            if (! $anonymous) {
                $this->environment->setLinkTarget(new LinkTarget($link, $url));
            }
        }

        $id = $this->spanProcessor->generateId();
        $this->spanProcessor->addToken(new SpanToken($this, $id, [
            'type' => 'link',
            'link' => $link,
            'url' => $url,
        ]));

        return $prev . $id . $next;
    }
}
