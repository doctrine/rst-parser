<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

abstract class CodeNode extends BlockNode
{
    protected $raw      = false;
    protected $language = null;

    public function setLanguage($language = null) : void
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRaw($raw) : void
    {
        $this->raw = $raw;
    }
}
