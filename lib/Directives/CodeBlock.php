<?php

declare(strict_types=1);

namespace Gregwar\RST\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Nodes\CodeNode;
use Gregwar\RST\Parser;
use function trim;

/**
 * Renders a code block, example:
 *
 * .. code-block:: php
 *
 *      <?php
 *
 *      echo "Hello world!\n";
 */
class CodeBlock extends Directive
{
    public function getName() : string
    {
        return 'code-block';
    }

    public function process(Parser $parser, $node, $variable, $data, array $options) : void
    {
        if (! $node) {
            return;
        }

        $kernel = $parser->getKernel();

        if ($node instanceof CodeNode) {
            $node->setLanguage(trim($data));
        }

        if ($variable) {
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
