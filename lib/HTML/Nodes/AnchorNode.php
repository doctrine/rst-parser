<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Nodes\AnchorNode as Base;

class AnchorNode extends Base
{
    public function render() : string
    {
        return '<a id="' . $this->value . '"></a>';
    }
}
