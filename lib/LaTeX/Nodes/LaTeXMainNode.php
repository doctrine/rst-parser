<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\Node as Base;

class LaTeXMainNode extends Base
{
    protected function doRender() : string
    {
        return '';
    }
}
