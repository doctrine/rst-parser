<?php

declare(strict_types=1);

namespace Doctrine\RST\References;

use Doctrine\RST\Environment;
use Doctrine\RST\Reference;

class Doc extends Reference
{
    /** @var string */
    private $name;

    /** @var Resolver */
    private $resolver;

    public function __construct(string $name = 'doc')
    {
        $this->name     = $name;
        $this->resolver = new Resolver();
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function resolve(Environment $environment, string $data) : ResolvedReference
    {
        return $this->resolver->resolve($environment, $data);
    }

    public function found(Environment $environment, string $data) : void
    {
        $environment->addDependency($data);
    }
}
