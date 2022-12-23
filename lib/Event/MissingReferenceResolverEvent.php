<?php

declare(strict_types=1);

namespace Doctrine\RST\Event;

use Doctrine\Common\EventArgs;
use Doctrine\RST\Environment;
use Doctrine\RST\References\ResolvedReference;

final class MissingReferenceResolverEvent extends EventArgs
{
    public const MISSING_REFERENCE_RESOLVER = 'resolveMissingReference';

    /** @var Environment */
    private $environment;

    /** @var string */
    private $data;

    /** @var string[] */
    private $attributes;

    /** @var ?ResolvedReference */
    private $resolvedReference = null;

    /** @param string[] $attributes */
    public function __construct(
        Environment $environment,
        string $data,
        array $attributes
    ) {
        $this->environment = $environment;
        $this->data        = $data;
        $this->attributes  = $attributes;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getData(): string
    {
        return $this->data;
    }

    /** @return string[] */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getResolvedReference(): ?ResolvedReference
    {
        return $this->resolvedReference;
    }

    public function setResolvedReference(?ResolvedReference $resolvedReference): void
    {
        $this->resolvedReference = $resolvedReference;
    }
}
