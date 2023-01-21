<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\References\ResolvedReference;

use function trim;

abstract class LinkRenderer
{
    protected Environment $environment;

    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }

    /** @param mixed[] $attributes */
    abstract public function renderUrl(?string $url, string $title, array $attributes = []): string;

    /** @param mixed[] $value */
    public function renderReference(ResolvedReference $reference, array $value): string
    {
        $text = (bool) $value['text'] ? $value['text'] : ($reference->getTitle() ?? '');
        $text = trim($text);
        // reference to another document
        if ($reference->getUrl() !== null) {
            $url = $reference->getUrl();

            if ($value['anchor'] !== null) {
                $url .= '#' . $value['anchor'];
            }

            $link = $this->renderUrl($url, $text, $reference->getAttributes());

            // reference to anchor in existing document
        } elseif ($value['url'] !== null) {
            $linkTarget = $this->environment->getLinkTarget($value['url']);

            $link = $this->renderUrl($linkTarget->getUrl(), $text, $reference->getAttributes());
        } else {
            $link = $this->renderUrl('#', $text . ' (unresolved reference)', $reference->getAttributes());
        }

        return $link;
    }
}
