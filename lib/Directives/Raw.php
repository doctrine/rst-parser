<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

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
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ) : void {
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
