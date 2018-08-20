<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Nodes;

use Gregwar\RST\Nodes\MetaNode as Base;

class MetaNode extends Base
{
    public function render() : string
    {
        return '';
    }
}
