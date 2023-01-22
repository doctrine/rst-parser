<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Builder;
use Doctrine\RST\Configuration;

/**
 * This event is called after the Builders initialization is finished,
 * before the actual build starts.
 *
 * It replaces the former method Kernel::initBuilder()
 */
class PostBuilderInitEvent extends EventArgs
{
    public const POST_BUILDER_INIT = 'postBuilderInit';

    private Configuration $configuration;
    private Builder $builder;

    public function __construct(Configuration $configuration, Builder $builder)
    {
        $this->configuration = $configuration;
        $this->builder       = $builder;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function getBuilder(): Builder
    {
        return $this->builder;
    }
}
