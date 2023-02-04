<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\TextRoles\LinkTextRole as AbstractLinkTextRole;

use function is_string;
use function substr;

final class LinkTextRole extends AbstractLinkTextRole
{
    /** @param mixed[] $attributes */
    public function renderLink(Environment $environment, ?string $url, string $title, array $attributes = []): string
    {
        $type = 'href';
        if (is_string($url) && $url !== '' && $url[0] === '#') {
            $type = 'ref';

            $url = substr($url, 1);
            $url = $url !== '' ? '#' . $url : '';
            $url = $environment->getUrl() . $url;
        }

        return $environment->getTemplateRenderer()->render('textroles/link.tex.twig', [
            'type' => $type,
            'url' => $url,
            'title' => $title,
            'attributes' => $attributes,
        ]);
    }
}
