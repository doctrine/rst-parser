<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

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
    /**
     * The name of the reference, i.e the :something:
     */
    abstract public function getName(): string;

    abstract public function process(string $text): string;
}
