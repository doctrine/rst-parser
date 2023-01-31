<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;

/**
 * This class offers a convenient way to implement a text role.
 */
abstract class BaseTextRole implements TextRole
{
    /** @var string[] */
    protected array $aliases = [];

    /**
     * Processes the text content of the role, that is the part between the backticks.
     * Returns an array containing the data available to the rendering.
     *
     * @return array<string, string>
     */
    public function process(Environment $environment, string $text): array
    {
        return [
            'section' => $this->getName(),
            'text' => $text,
        ];
    }

    /** @param array<string, string> $parameters */
    protected function renderTemplate(Environment $environment, string $template, array $parameters = []): string
    {
        $templateName = $template . '.' . $environment->getConfiguration()->getFileExtension() . '.twig';

        return $environment->getConfiguration()->getTemplateRenderer()->render($templateName, $parameters);
    }

    /** @return String[] */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /** @param string[] $aliases */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }
}
