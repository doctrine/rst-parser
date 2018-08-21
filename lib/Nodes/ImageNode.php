<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

abstract class ImageNode extends Node
{
    /** @var string */
    protected $url;

    /** @var string[] */
    protected $options;

    /**
     * @param string[] $options
     */
    public function __construct(string $url, array $options = [])
    {
        parent::__construct();

        $this->url     = $url;
        $this->options = $options;
    }
}
