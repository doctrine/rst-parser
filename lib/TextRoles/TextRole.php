<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanToken;

/**
 * A role or "custom interpreted text role" is an inline piece of explicit markup. It signifies
 * that the enclosed text should be interpreted in a specific way.
 *
 * The general syntax is :rolename:`content`. See also https://www.sphinx-doc.org/en/master/usage/restructuredtext/basics.html#roles
 *
 * Most text roles extend BaseTextRole. References that are rendered into local links should extend ReferenceRole.
 */
interface TextRole
{
    /**
     * The name of the text-role, i.e the :something:
     */
    public function getName(): string;

    /**
     * Some text roles have a short and a long name. The alternative names can be registered as alias.
     * For example ``:bold:`bold text``` and ``:b:`bold text``` can have the same effect.
     *
     * @return string[]
     */
    public function getAliases(): array;

    /**
     * Processes the text content of the role, that is the part between the backticks.
     * Returns an array containing the data available to the rendering.
     *
     * @return array<string, string>
     */
    public function process(Environment $environment, string $text): array;

    /**
     * Renders the text role.
     *
     * If you want to support several formats (HTML and LaTEX) rendering needs to take care of this
     */
    public function render(Environment $environment, SpanToken $spanToken): string;

    /**
     * Does this text role have a special syntax like ``*cursive*``?
     */
    public function hasSpecialSyntax(): bool;

    public function getTokens(SpanProcessor $spanProcessor, string $span): string;
}
