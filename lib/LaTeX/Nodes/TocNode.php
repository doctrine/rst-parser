<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX\Nodes;

use Doctrine\RST\Nodes\TocNode as Base;

class TocNode extends Base
{
    protected function doRender() : string
    {
        $tex = '\tableofcontents' . "\n";

        foreach ($this->files as $file) {
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
