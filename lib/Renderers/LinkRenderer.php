<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\References\ResolvedReference;

use function trim;

abstract class LinkRenderer extends Renderer
{
    /** @param mixed[] $attributes */
    abstract public function renderUrl(?string $url, string $title, array $attributes = []): string;

    /** @param mixed[] $value */
    public function renderReference(ResolvedReference $reference, array $value): string
    {
        $text = $value['text'] ?? ($reference->getTitle() ?? '');
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
