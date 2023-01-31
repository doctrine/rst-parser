<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\References\Resolver;

final class Doc extends ReferenceRole
{
    /** @var string */
    private $name;

    /** @var Resolver */
    private $resolver;

    /**
     * Used with "ref" - it means the dependencies added in found()
     * must be resolved to their final path later (they are not
     * already document names).
     *
     * @var bool
     */
    private $dependenciesMustBeResolved;

    public function __construct(string $name = 'doc', bool $dependenciesMustBeResolved = false)
    {
        $this->name                       = $name;
        $this->resolver                   = new Resolver();
        $this->dependenciesMustBeResolved = $dependenciesMustBeResolved;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function resolve(Environment $environment, string $data): ?ResolvedReference
    {
        $resolvedReference = $this->resolver->resolve($environment, $this->getName(), $data);
        if ($resolvedReference === null) {
            $environment->addInvalidReference($data);

            return null;
        }

        $environment->resolveDependency($data, $resolvedReference);

        return $resolvedReference;
    }

    /**
     * Processes the text content of the role, that is the part between the backticks.
     * Returns an array containing the data available to the rendering.
     *
     * @return array<string, string>
     */
    public function process(Environment $environment, string $text): array
    {
        $data = parent::process($environment, $text);
        $environment->addDependency($data['url'], $this->dependenciesMustBeResolved);

        return $data;
    }
}
