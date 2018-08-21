<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\LaTeX\Nodes\MetaNode;
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Parser;

/**
 * Add a meta information:
 *
 * .. meta::
 *      :key: value
 */
class Meta extends Directive
{
    public function getName() : string
    {
        return 'meta';
    }

    /**
     * @param string[] $options
     */
    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options) : void
    {
        $document = $parser->getDocument();

        foreach ($options as $key => $value) {
            $meta = new MetaNode($key, $value);
            $document->addHeaderNode($meta);
        }

        if ($node === null) {
            return;
        }

        $document->addNode($node);
    }
}
