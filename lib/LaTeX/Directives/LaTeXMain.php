<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Directives;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Marks the document as LaTeX main
 */
final class LaTeXMain extends Directive
{
    public function getName(): string
    {
        return 'latex-main';
    }

    /**
     * @param string[] $options
     */
    public function processNode(
        Parser $parser,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        return $parser->getNodeFactory()->createMainNode();
    }
}
