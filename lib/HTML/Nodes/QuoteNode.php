<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    public function render() : string
    {
        return '<blockquote>' . $this->value . '</blockquote>';
    }
}
