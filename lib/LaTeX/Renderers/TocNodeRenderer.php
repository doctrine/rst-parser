<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Renderers\NodeRenderer;

class TocNodeRenderer implements NodeRenderer
{
    /** @var Environment */
    private $environment;

    /** @var TocNode */
    private $tocNode;

    public function __construct(Environment $environment, TocNode $tocNode)
    {
        $this->environment = $environment;
        $this->tocNode     = $tocNode;
    }

    public function render() : string
    {
        $tex = '\tableofcontents' . "\n";

        foreach ($this->tocNode->getFiles() as $file) {
            $reference = $this->environment->resolve('doc', $file);

            if ($reference === null) {
                continue;
            }

            $url = $this->environment->relativeUrl($reference->getUrl());

            $tex .= '\\input{' . $url . "}\n";
        }

        return $tex;
    }
}
