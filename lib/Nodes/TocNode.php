<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Environment;

abstract class TocNode extends Node
{
    /** @var Environment */
    protected $environment;

    /** @var string[] */
    protected $files;

    /** @var string[] */
    protected $options;

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function __construct(Environment $environment, array $files, array $options)
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
