<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\AnchorNode as Base;

class AnchorNode extends Base
{
    public function render() : string
    {
        return '\label{' . $this->value . '}';
    }
}
