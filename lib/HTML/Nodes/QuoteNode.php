<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\QuoteNode as Base;

class QuoteNode extends Base
{
    protected function doRender() : string
    {
        return '<blockquote>' . $this->value . '</blockquote>';
    }
}
