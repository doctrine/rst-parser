<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Doctrine\RST\Toc\GlobSearcher;
use Doctrine\RST\Toc\ToctreeBuilder;

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
        $environment = $parser->getEnvironment();
        $contentsNode = $parser->getNodeFactory()
            ->createContentsNode($environment, $options);
        $parser->getDocument()->addNode($contentsNode);
    }
}
