<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Environment;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Span\SpanToken;

/**
 * A reference is something that can be resolved in the document, for instance:
 *
 * :method:`helloWorld()`
 *
 * Will be resolved as a reference of type method and the given reference will
 * be called to resolve it
 */
abstract class ReferenceRole extends TextRole
{
    public const TYPE_REFERENCE = 'ref';
    public const TYPE_DOCUMENT  = 'doc';

    /**
     * Resolve the reference and returns an array
     *
     * @param Environment $environment the Environment in use
     * @param string      $data        the data of the reference
     */
    abstract public function resolve(Environment $environment, string $data): ?ResolvedReference;

    public function process(Environment $environment, SpanToken $spanToken): string
    {
        $resolvedReference = $this->resolve($environment, $spanToken->get('url'));

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
