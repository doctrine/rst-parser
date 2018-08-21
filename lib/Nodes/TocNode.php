<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

use Gregwar\RST\Environment;

abstract class TocNode extends Node
{
    /** @var string[] */
    protected $files;

    /** @var Environment */
    protected $environment;

    /** @var string[] */
    protected $options;

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function __construct(array $files, Environment $environment, array $options)
    {
        parent::__construct();

        $this->files       = $files;
        $this->environment = $environment;
        $this->options     = $options;
    }

    /**
     * @return string[]
     */
    public function getFiles() : array
    {
        return $this->files;
    }
}
