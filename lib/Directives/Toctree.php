<?php

declare(strict_types=1);

namespace Doctrine\RST\Directives;

use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Doctrine\RST\Toc\GlobSearcher;
use Doctrine\RST\Toc\ToctreeBuilder;

use function assert;

class Toctree extends Directive
{
    /** @var ToctreeBuilder */
    private $toctreeBuilder;

    public function __construct()
    {
        $this->toctreeBuilder = new ToctreeBuilder(new GlobSearcher());
    }

    public function getName(): string
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
    ): void {
        if ($node === null) {
            return;
        }

        assert($node instanceof CodeNode, 'Toctree should be passed a CodeNode only');

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

    /**
     * This directive includes the "code formatting" where the
     * "value" of the block are indented as lines inside the directive.
     *
     * This will cause a CodeNode to passed to proces().
     * See CodeNode for more details.
     */
    public function wantCode(): bool
    {
        return true;
    }
}
