<?php

declare(strict_types=1);

namespace Gregwar\RST;

use Gregwar\RST\Nodes\Node;
use Gregwar\RST\References\Doc;

abstract class Kernel
{
    abstract protected function getName() : string;

    public function getClass(string $name) : string
    {
        return 'Gregwar\RST\\' . $this->getName() . '\\' . $name;
    }

    /**
     * @param mixed $arg1
     * @param mixed $arg2
     * @param mixed $arg3
     * @param mixed $arg4
     *
     * @return Node|Environment
     */
    public function build(string $name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $class = $this->getClass($name);

        return new $class($arg1, $arg2, $arg3, $arg4);
    }

    /**
     * @return Directive[]
     */
    public function getDirectives() : array
    {
        return [
            new Directives\Dummy(),
            new Directives\CodeBlock(),
            new Directives\Raw(),
            new Directives\Replace(),
            new Directives\Toctree(),
            new Directives\Document(),
            new Directives\RedirectionTitle(),
        ];
    }

    /**
     * @return Doc[]
     */
    public function getReferences() : array
    {
        return [
            new References\Doc(),
            new References\Doc('ref'),
        ];
    }

    public function postParse(Document $document) : void
    {
    }

    public function initBuilder(Builder $builder) : void
    {
    }

    public function getFileExtension() : string
    {
        return 'txt';
    }
}
