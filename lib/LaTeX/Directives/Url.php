<?php

declare(strict_types=1);

namespace Gregwar\RST\LaTeX\Directives;

use Gregwar\RST\Directive;
use Gregwar\RST\Parser;
use function trim;

/**
 * Sets the document URL
 */
class Url extends Directive
{
    public function getName() : string
    {
        return 'url';
    }

    public function processAction(Parser $parser, $variabe, $data, array $options) : void
    {
        $environment = $parser->getEnvironment();
        $environment->setUrl(trim($data));
    }
}
