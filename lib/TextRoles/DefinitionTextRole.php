<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use function preg_match;
use function sprintf;
use function trim;

/**
 * A text role which wraps a text and optionally an additional definition in
 * brackets. Can be used for abbreviations, tooltips, etc
 *
 * Example:
 * ```
 * :abbreviation:`LIFO (last-in, first-out)`
 * ```
 */
class DefinitionTextRole extends TextRole
{
    private string $name;
    private string $wrap;

    /** @param string[] $aliases */
    public function __construct(string $name, string $wrap, array $aliases = [])
    {
        $this->name = $name;
        $this->wrap = $wrap;
        $this->setAliases($aliases);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function process(string $text): string
    {
        $definition = '';
        if (preg_match('/(.+)\(([^\)]+)\)$/', $text, $matches) !== 0) {
            $text       =  trim($matches[1]);
            $definition = trim($matches[2]);
        }

        return sprintf($this->wrap, $text, $definition);
    }
}
