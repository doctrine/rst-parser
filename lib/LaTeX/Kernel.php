<?php

declare(strict_types=1);

namespace Doctrine\RST\LaTeX;

use Doctrine\RST\Directive;
use Doctrine\RST\Kernel as Base;
use function array_merge;

class Kernel extends Base
{
    public function getName() : string
    {
        return 'LaTeX';
    }

    /**
     * @return Directive[]
     */
    public function getDirectives() : array
    {
        $directives = parent::getDirectives();

        $directives = array_merge($directives, [
            new Directives\LaTeXMain(),
            new Directives\Image(),
            new Directives\Meta(),
            new Directives\Stylesheet(),
            new Directives\Title(),
            new Directives\Url(),
            new Directives\Wrap('note'),
        ]);

        return $directives;
    }

    public function getFileExtension() : string
    {
        return 'tex';
    }
}
