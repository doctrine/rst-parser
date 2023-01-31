<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;

class WrapperTextRole extends BaseTextRole
{
    private string $name;
    private string $templateName;

    /** @param string[] $aliases Aliases for the name */
    public function __construct(string $name, ?string $templateName = null, array $aliases = [])
    {
        $this->name         = $name;
        $this->templateName = $templateName ?? 'textroles/' . $this->name . '.html.twig';
        $this->setAliases($aliases);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        return $this->renderTemplate(
            $environment,
            $this->templateName,
            ['text' => $spanToken->get('text')]
        );
    }
}
