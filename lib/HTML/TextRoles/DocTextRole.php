<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\TextRoles\Doc;

final class DocTextRole extends Doc
{
    /** @param mixed[] $attributes */
    public function renderLink(Environment $environment, ?string $url, string $title, array $attributes = []): string
    {
        return $environment->getTemplateRenderer()->render('textroles/link.html.twig', [
            'url' => $environment->generateUrl((string) $url),
            'title' => $title,
            'attributes' => $attributes,
        ]);
    }
}
