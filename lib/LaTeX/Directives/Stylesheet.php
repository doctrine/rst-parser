<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Directives;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Adds a stylesheet to a document, example:
 *
 * .. stylesheet:: style.css
 */
final class Stylesheet extends Directive
{
    public function getName(): string
    {
        return 'stylesheet';
    }

    /**
     * @param string[] $options
     */
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): void {
    }
}
