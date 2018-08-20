<?php

namespace Gregwar\RST\HTML\Nodes;

use Gregwar\RST\Environment;
use Gregwar\RST\Nodes\TitleNode as Base;

class TitleNode extends Base
{
    public function render()
    {
        $anchor = Environment::slugify($this->value);

        return '<a id="'.$anchor.'"></a><h'.$this->level.'>'.$this->value.'</h'.$this->level.">";
    }
}
