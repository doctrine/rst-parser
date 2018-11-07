<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\SeparatorNode as Base;

class SeparatorNode extends Base
{
    protected function doRender() : string
    {
        return '\\ \\';
    }
}
