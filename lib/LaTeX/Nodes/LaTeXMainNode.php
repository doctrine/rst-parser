<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\Node as Base;

class LaTeXMainNode extends Base
{
    public function render() : string
    {
        return '';
    }
}
