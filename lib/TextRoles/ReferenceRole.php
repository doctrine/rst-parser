<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\InvalidLink;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Span\SpanToken;

use function preg_match;
use function trim;

/**
 * This class offers a convenient way to implement a reference. A reference is a text role that is
 * rendered into a local link, for example ``:doc:`See here <SubDir/index>```
 */
abstract class ReferenceRole extends BaseTextRole
{
    public const TYPE_REFERENCE = 'ref';
    public const TYPE_DOCUMENT  = 'doc';

    /**
     * Resolve the reference and returns an array
     *
     * @param Environment $environment the Environment in use
     * @param string      $data        the data of the reference
     */
    abstract public function resolve(Environment $environment, string $data): ?ResolvedReference;

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        $resolvedReference = $this->resolve($environment, $spanToken->get('url'));
        if ($resolvedReference === null) {
            $environment->addInvalidLink(new InvalidLink($spanToken->get('url')));

            return $spanToken->get('linktext');
        }

        return $this->renderReference($environment, $resolvedReference, $spanToken->getTokenData());
    }

    /** @param mixed[] $value */
    public function renderReference(Environment $environment, ResolvedReference $reference, array $value): string
    {
        $text = $value['linktext'] ?? ($reference->getTitle() ?? '');
        $text = trim($text);
        // reference to another document
        if ($reference->getUrl() !== null) {
            $url = $reference->getUrl();

            if ($value['anchor'] !== null) {
                $url .= '#' . $value['anchor'];
            }

            $link = $this->renderLink($environment, $url, $text, $reference->getAttributes());

            // reference to anchor in existing document
        } elseif ($value['url'] !== null) {
            $linkTarget = $environment->getLinkTarget($value['url']);

            $link = $this->renderLink($environment, $linkTarget->getUrl(), $text, $reference->getAttributes());
        } else {
            $link = $this->renderLink($environment, '#', $text . ' (unresolved reference)', $reference->getAttributes());
        }

        return $link;
    }

    /** @param mixed[] $attributes */
    abstract public function renderLink(Environment $environment, ?string $url, string $title, array $attributes = []): string;

    /**
     * Processes the text content of a reference role. The url and the linktext, if found, are set.
     * If no linktext is found it is set to null.
     *
     * For example for `See here <SubDir/index>` "SubDir/index" is the url and "See here" the link text.
     * For `somereference` the url is "somereference" and the linktext is null.
     *
     * @return array<string, string>
     */
    public function process(Environment $environment, string $text): array
    {
        $anchor = null;

        $url      = $text;
        $linktext = null;
        if (preg_match('/^(.+)<(.+)>$/mUsi', $text, $match) > 0) {
            $linktext = trim($match[1]);
            $url      = trim($match[2]);
        }

        if (preg_match('/^(.+)#(.+)$/mUsi', $url, $match) > 0) {
            $url    = $match[1];
            $anchor = $match[2];
        }

        return [
            'section' => $this->getName(),
            'url' => $url,
            'text' => $text,
            'linktext' => $linktext,
            'anchor' => $anchor,
        ];
    }
}
