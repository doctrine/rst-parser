<?php

declare(strict_types=1);

namespace Gregwar\RST\HTML;

use Gregwar\RST\Directive;
use Gregwar\RST\Kernel as Base;
use function array_merge;

class Kernel extends Base
{
    public function getName() : string
    {
        return 'HTML';
    }

    /**
     * @return Directive[]
     */
    public function getDirectives() : array
    {
        $directives = parent::getDirectives();

        $directives = array_merge($directives, [
            new Directives\Image(),
            new Directives\Figure(),
            new Directives\Meta(),
            new Directives\Stylesheet(),
            new Directives\Title(),
            new Directives\Url(),
            new Directives\Div(),
            new Directives\Wrap('note'),
        ]);

        return $directives;
    }

    public function getFileExtension() : string
    {
        return 'html';
    }
}
