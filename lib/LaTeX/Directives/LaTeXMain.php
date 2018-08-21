<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\LaTeX\Nodes\LaTeXMainNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Marks the document as LaTeX main
 */
class LaTeXMain extends Directive
{
    public function getName() : string
    {
        return 'latex-main';
    }

    /**
     * @param string[] $options
     */
    public function processNode(Parser $parser, string $variable, string $data, array $options) : ?Node
    {
        return new LaTeXMainNode();
    }
}
