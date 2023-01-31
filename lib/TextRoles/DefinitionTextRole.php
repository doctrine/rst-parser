<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\Span\SpanToken;

use function array_merge;
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
class DefinitionTextRole extends BaseTextRole
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

    /**
     * Processes the text content of a definition role. The term and the definition are set.
     *
     * For example for `LIFO (last-in, first-out)` "LIFO" would be the term and "last-in, first-out"
     * the definition.
     *
     * @return array<string, string>
     */
    public function process(Environment $environment, string $text): array
    {
        $data       = parent::process($environment, $text);
        $term       = '';
        $definition = '';
        if (preg_match('/(.+)\(([^\)]+)\)$/', $text, $matches) !== 0) {
            $term       =  trim($matches[1]);
            $definition = trim($matches[2]);
        }

        $data = array_merge($data, [
            'term' => $term,
            'definition' => $definition,
        ]);

        return $data;
    }

    public function render(Environment $environment, SpanToken $spanToken): string
    {
        return sprintf($this->wrap, $spanToken->get('term'), $spanToken->get('definition'));
    }
}
