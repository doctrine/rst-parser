<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\Directive;
use Doctrine\RST\Parser;

use function trim;

/**
 * Sets the document URL
 */
final class Url extends Directive
{
    public function getName(): string
    {
        return 'url';
    }

    /** @param string[] $options */
    public function processAction(
        Parser $parser,
        string $variable,
        string $data,
        array $options
    ): void {
        $environment = $parser->getEnvironment();

        $environment->setUrl(trim($data));
    }
}
