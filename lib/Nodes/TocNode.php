<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Environment;
use Symfony\Component\Filesystem\Path;

use function array_map;

class TocNode extends Node
{
    private const DEFAULT_DEPTH = 2;

    /** @var Environment */
    protected $environment;

    /** @var string[] */
    private $files;

    /** @var string[] */
    private $options;

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function __construct(Environment $environment, array $files, array $options)
    {
        parent::__construct();

        $this->files       = array_map([Path::class, 'normalize'], $files);
        $this->environment = $environment;
        $this->options     = $options;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /** @return string[] */
    public function getFiles(): array
    {
        return $this->files;
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

        if (isset($this->options['maxdepth'])) {
            return (int) $this->options['maxdepth'];
        }

        return self::DEFAULT_DEPTH;
    }

    public function isTitlesOnly(): bool
    {
        return isset($this->options['titlesonly']);
    }
}
