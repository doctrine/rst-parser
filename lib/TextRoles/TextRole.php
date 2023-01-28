<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;

/**
 * A text role is a string that is styled in a certain way
 *
 * :php:`helloWorld()`
 *
 * Will be rendered as
 *
 * <code class="php">helloWorld()</code>
 */
abstract class TextRole
{
    /** @var String[] */
    protected array $aliases = [];

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

    /**
     * The name of the text-role, i.e the :something:
     */
    abstract public function getName(): string;

    abstract public function process(Environment $environment, string $text): string;
}
