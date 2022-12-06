<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Environment;

class ContentsNode extends Node
{

    private const DEFAULT_DEPTH = 999;

    /** @var Environment */
    protected $environment;

    /** @var string[] */
    private $options;

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function __construct(Environment $environment, array $options)
    {
        parent::__construct();

        $this->environment = $environment;
        $this->options     = $options;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /** @return string[] */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDepth(): int
    {
        if (isset($this->options['depth'])) {
            return (int) $this->options['depth'];
        }

        return self::DEFAULT_DEPTH;
    }
}