<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    public function render() : string
    {
        return "\\begin{quotation}\n" . $this->value . "\n\\end{quotation}\n";
    }
}
