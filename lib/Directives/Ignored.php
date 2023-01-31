<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Many documents contain directives that should not be interpreted anymore.
 * You can use this class to register a directive that is doing nothing and
 * just ignored.
 *
 * ```
 * [
 *     //...
 *     new IgnoredDirective('directive-to-be-ignored'),
 * ]
 * ```
 */
final class Ignored extends Directive
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @param string[] $options */
    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options): void
    {
        // This method overrides default behaviour of Directive
        // It must be implemented to prevent processing of content.
    }
}
