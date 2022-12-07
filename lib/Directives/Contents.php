<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

final class Contents extends Directive
{
    public function __construct()
    {
    }

    public function getName(): string
    {
        return 'contents';
    }

    /** @param string[] $options */
    public function process(
        Parser $parser,
        ?Node $node,
        string $variable,
        string $data,
        array $options
    ): void {
        $environment  = $parser->getEnvironment();
        $documentNode = $parser->getDocument();
        $contentsNode = $parser->getNodeFactory()
            ->createContentsNode($environment, $documentNode, $options);
        $parser->getDocument()->addNode($contentsNode);
    }
}
