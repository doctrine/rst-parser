<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

final class ImageNode extends Node
{
    /** @var string */
    private $url;

    /** @var string[] */
    private $options;

    /**
     * @param string[] $options
     */
    public function __construct(string $url, array $options = [])
    {
        parent::__construct();

        $this->url     = $url;
        $this->options = $options;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }
}
