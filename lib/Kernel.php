<?php

declare(strict_types=1);

namespace Gregwar\RST;

abstract class Kernel
{
    /**
     * Get the name of the kernel
     */
    abstract function getName() : string;

    /**
     * Gets the class for the given name
     */
    public function getClass($name)
    {
        return 'Gregwar\RST\\' . $this->getName() . '\\' . $name;
    }

    /**
     * Create an instance of some class
     */
    public function build($name, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
    {
        $class = $this->getClass($name);

        if ($class) {
            return new $class($arg1, $arg2, $arg3, $arg4);
        }

        return null;
    }

    /**
     * Gets the available directives
     */
    public function getDirectives()
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
     * Document references
     */
    public function getReferences()
    {
        return [
            new References\Doc(),
            new References\Doc('ref'),
        ];
    }

    /**
     * Allowing the kernel to tweak document after the build
     */
    public function postParse(Document $document) : void
    {
    }

    /**
     * Allowing the kernel to tweak the builder
     */
    public function initBuilder(Builder $builder) : void
    {
    }

    /**
     * Get the output files extension
     */
    public function getFileExtension()
    {
        return 'txt';
    }
}
