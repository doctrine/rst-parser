<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\TitleNode as Base;

class TitleNode extends Base
{
    protected function doRender() : string
    {
        $type = 'chapter';

        if ($this->level > 1) {
            $type = 'section';

            for ($i=2; $i<$this->level; $i++) {
                $type = 'sub' . $type;
            }
        }

        return '\\' . $type . '{' . $this->value . '}';
    }
}
