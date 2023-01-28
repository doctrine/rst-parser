<?php

declare(strict_types=1);

namespace Doctrine\RST\References;

use Doctrine\RST\Environment;
use Doctrine\RST\TextRoles\TextRole;

/**
 * A reference is something that can be resolved in the document, for instance:
 *
 * :method:`helloWorld()`
 *
 * Will be resolved as a reference of type method and the given reference will
 * be called to resolve it
 */
abstract class Reference extends TextRole
{
    /**
     * Resolve the reference and returns an array
     *
     * @param Environment $environment the Environment in use
     * @param string      $data        the data of the reference
     */
    abstract public function resolve(Environment $environment, string $data): ?ResolvedReference;

    final public function process(Environment $environment, string $text): string
    {
        $resolvedReference = $this->resolve($environment, $text);

        return '';
    }

    /**
     * Called when a reference is just found
     *
     * @param Environment $environment the Environment in use
     * @param string      $data        the data of the reference
     */
    public function found(Environment $environment, string $data): void
    {
    }
}
