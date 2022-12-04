<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

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
final class CodeBlock extends Directive
{
    public function getName(): string
    {
        return 'code-block';
    }

    /** @param string[] $options */
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): void {
        if ($node === null) {
            return;
        }

        $kernel = $parser->getKernel();

        if ($node instanceof CodeNode) {
            $node->setLanguage(trim($data));
            $node->setOptions($options);
        }

        if ($variable !== '') {
            $environment = $parser->getEnvironment();
            $environment->setVariable($variable, $node);
        } else {
            $document = $parser->getDocument();
            $document->addNode($node);
        }
    }

    public function wantCode(): bool
    {
        return true;
    }
}
