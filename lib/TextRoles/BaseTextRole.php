<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;

/**
 * This class offers a convenient way to implement a text role.
 */
abstract class BaseTextRole implements TextRole
{
    /** @var String[] */
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

    /** @return String[] */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /** @param String[] $aliases */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;
    }
}
