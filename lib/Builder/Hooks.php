<?php

declare(strict_types=1);

namespace Doctrine\RST\Builder;

use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Parser;

class Hooks
{
    /** @var callable[] */
    private $beforeHooks = [];

    /** @var callable[] */
    private $hooks = [];

    public function addHook(callable $callable) : self
    {
        $this->hooks[] = $callable;

        return $this;
    }

    public function addBeforeHook(callable $callable) : self
    {
        $this->beforeHooks[] = $callable;

        return $this;
    }

    public function callHooks(DocumentNode $document) : void
    {
        foreach ($this->hooks as $hook) {
            $hook($document);
        }
    }

    public function callBeforeHooks(Parser $parser) : void
    {
        foreach ($this->beforeHooks as $hook) {
            $hook($parser);
        }
    }
}
