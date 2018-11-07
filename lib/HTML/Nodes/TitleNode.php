<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TitleNode as Base;

class TitleNode extends Base
{
    protected function doRender() : string
    {
        $anchor = Environment::slugify((string) $this->value);

        return '<a id="' . $anchor . '"></a><h' . $this->level . '>' . $this->value . '</h' . $this->level . '>';
    }
}
