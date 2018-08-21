<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    public function render() : string
    {
        return '<blockquote>' . $this->value . '</blockquote>';
    }
}
