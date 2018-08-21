<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\MetaNode as Base;

class MetaNode extends Base
{
    public function render() : string
    {
        return '';
    }
}
