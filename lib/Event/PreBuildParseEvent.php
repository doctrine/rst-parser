<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\RST\Builder;

final class PreBuildParseEvent extends BuildEvent
{
    public const PRE_BUILD_PARSE = 'preBuildParse';

    /** @var Builder\ParseQueue */
    private $parseQueue;

    public function __construct(Builder $builder, string $directory, string $targetDirectory, Builder\ParseQueue $parseQueue)
    {
        parent::__construct($builder, $directory, $targetDirectory);

        $this->parseQueue = $parseQueue;
    }

    public function getParseQueue(): Builder\ParseQueue
    {
        return $this->parseQueue;
    }
}
