<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\RefInsideDirective;

use Doctrine\RST\Directive;
use Doctrine\RST\HTML\Kernel;
use function array_merge;

/**
 * Class HtmlKernel
 */
class HtmlKernel extends Kernel
{
    /**
     * @return Directive[]
     */
    public function getDirectives() : array
    {
        return array_merge(
            parent::getDirectives(),
            [
                new VersionAddedDirective(),
            ]
        );
    }
}
