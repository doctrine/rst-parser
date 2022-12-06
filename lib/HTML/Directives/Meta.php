<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
final class Meta extends Directive
{
    public function getName(): string
    {
        return 'meta';
    }

    /** @param string[] $options */
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): void {
        $document = $parser->getDocument();

        $nodeFactory = $parser->getNodeFactory();

        foreach ($options as $key => $value) {
            $meta = $nodeFactory->createMetaNode($key, $value);

            $document->addHeaderNode($meta);
        }

        if ($node === null) {
            return;
        }

        $document->addNode($node);
    }
}
