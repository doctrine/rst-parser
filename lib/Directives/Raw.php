<?php

declare(strict_types=1);

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\CodeNode;
use Gregwar\RST\Nodes\Node;
use Gregwar\RST\Parser;

/**
 * Renders a raw block, example:
 *
 * .. raw::
 *
 *      <u>Undelined!</u>
 */
class Raw extends Directive
{
    public function getName() : string
    {
        return 'raw';
    }

    /**
     * @param string[] $options
     */
    public function process(Parser $parser, ?Node $node, string $variable, string $data, array $options) : void
    {
        if ($node === null) {
            return;
        }

        $kernel = $parser->getKernel();

        if ($node instanceof CodeNode) {
            $node->setRaw(true);
        }

        if ($variable !== '') {
            $environment = $parser->getEnvironment();
            $environment->setVariable($variable, $node);
        } else {
            $document = $parser->getDocument();
            $document->addNode($node);
        }
    }

    public function wantCode() : bool
    {
        return true;
    }
}
