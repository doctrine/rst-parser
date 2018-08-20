<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\LaTeX\Nodes\LaTeXMainNode;
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

    public function processNode(Parser $parser, $variable, $data, array $options)
    {
        return new LaTeXMainNode();
    }
}
