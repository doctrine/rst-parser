<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    public function render() : string
    {
        return "\\begin{quotation}\n" . $this->value . "\n\\end{quotation}\n";
    }
}
