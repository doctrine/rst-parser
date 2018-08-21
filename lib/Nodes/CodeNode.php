<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

abstract class CodeNode extends BlockNode
{
    /** @var bool */
    protected $raw = false;

    /** @var null|string */
    protected $language = null;

    public function setLanguage(?string $language = null) : void
    {
        $this->language = $language;
    }

    public function getLanguage() : ?string
    {
        return $this->language;
    }

    public function setRaw(bool $raw) : void
    {
        $this->raw = $raw;
    }
}
