<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Renderers;

use Doctrine\RST\Renderers\LinkRenderer as AbstractLinkRenderer;

final class LinkRenderer extends AbstractLinkRenderer
{
    /** @param mixed[] $attributes */
    public function renderUrl(?string $url, string $title, array $attributes = []): string
    {
        return $this->environment->getTemplateRenderer()->render('link.html.twig', [
            'url' => $this->environment->generateUrl((string) $url),
            'title' => $title,
            'attributes' => $attributes,
        ]);
    }
}
