<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Nodes\SeparatorNode as Base;

class SeparatorNode extends Base
{
    public function render() : string
    {
        return '<hr />';
    }
}
