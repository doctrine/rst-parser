<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Renderers\NodeRenderer;

class CodeNodeRenderer implements NodeRenderer
{
    /** @var CodeNode */
    private $codeNode;

    public function __construct(CodeNode $codeNode)
    {
        $this->codeNode = $codeNode;
    }

    public function render() : string
    {
        $tex  = '\\lstset{language=' . $this->codeNode->getLanguage() . "}\n";
        $tex .= "\\begin{lstlisting}\n";
        $tex .= $this->codeNode->getValue() . "\n";
        $tex .= "\\end{lstlisting}\n";

        return $tex;
    }
}
