<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\RefInsideDirective;

use Doctrine\RST\Nodes\CallableNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;
use Doctrine\RST\SubDirective;
use function sprintf;
use function strip_tags;

class VersionAddedDirective extends SubDirective
{
    public function getName() : string
    {
        return 'versionadded';
    }

    /**
     * @param string[] $options
     */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ) : ?Node {
        return new CallableNode(static function () use ($data, $document) {
            return sprintf(
                '<div class="versionadded"><p><span class="versionmodified">New in version %s: </span>%s</p></div>',
                $data,
                strip_tags((string) $document, '<a><code>')
            );
        });
    }
}
