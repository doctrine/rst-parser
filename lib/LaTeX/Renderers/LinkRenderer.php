<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Renderers\LinkRenderer as AbstractLinkRenderer;

use function is_string;
use function substr;

final class LinkRenderer extends AbstractLinkRenderer
{
    /** @param mixed[] $attributes */
    public function renderUrl(?string $url, string $title, array $attributes = []): string
    {
        $type = 'href';
        if (is_string($url) && $url !== '' && $url[0] === '#') {
            $type = 'ref';

            $url = substr($url, 1);
            $url = $url !== '' ? '#' . $url : '';
            $url = $this->environment->getUrl() . $url;
        }

        return $this->environment->getTemplateRenderer()->render('link.tex.twig', [
            'type' => $type,
            'url' => $url,
            'title' => $title,
            'attributes' => $attributes,
        ]);
    }
}
