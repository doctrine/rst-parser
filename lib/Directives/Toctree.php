<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Directive;
use Doctrine\RST\GlobSearcher;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Doctrine\RST\ToctreeBuilder;

class Toctree extends Directive
{
    /** @var ToctreeBuilder */
    private $toctreeBuilder;

    public function __construct()
    {
        $this->toctreeBuilder = new ToctreeBuilder(new GlobSearcher());
    }

    public function getName() : string
    {
        return 'toctree';
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

        $environment = $parser->getEnvironment();

        $toctreeFiles = $this->toctreeBuilder
            ->buildToctreeFiles($environment, $node, $options);

        foreach ($toctreeFiles as $file) {
            $environment->addDependency($file);
        }

        $tocNode = $parser->getNodeFactory()
            ->createTocNode($environment, $toctreeFiles, $options);

        $parser->getDocument()->addNode($tocNode);
    }

    public function wantCode() : bool
    {
        return true;
    }
}
