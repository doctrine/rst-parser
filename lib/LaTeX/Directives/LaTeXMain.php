<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\LaTeX\Nodes\LaTeXMainNode;
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Parser;

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
