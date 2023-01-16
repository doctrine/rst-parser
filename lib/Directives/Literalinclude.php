<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

use function explode;
use function file_exists;
use function file_get_contents;
use function sprintf;
use function trim;

/**
 * Renders a code block from a file, example:
 *
 * ..  literalinclude:: _code/_Example.php
 *     :language: php
 */
final class Literalinclude extends Directive
{
    public function getName(): string
    {
        return 'literalinclude';
    }

    /** @param string[] $options */
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): void {
        $configuration = $parser->getEnvironment()->getConfiguration();
        if ($data === '') {
            $configuration->getErrorManager()
                ->warning('Directive literalinclude has no file to include specified');

            return;
        }

        $path = $parser->getEnvironment()->absoluteRelativePath(trim($data));
        if (! file_exists($path)) {
            $configuration->getErrorManager()
                ->warning(sprintf('Literalinclude "%s" does not exist.', $data));

            return;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            $configuration->getErrorManager()
                ->warning(sprintf('Literalinclude "%s" is not readable.', $data));

            return;
        }

        $codeNode = $configuration->getNodeFactory($parser->getEnvironment())->createCodeNode(explode("\n", $contents));
        $codeNode->setLanguage($options['language'] ?? 'none');
        $codeNode->setOptions($options);
        $codeNode->setRaw(false);

        $document = $parser->getDocument();
        $document->addNode($codeNode);
    }
}
