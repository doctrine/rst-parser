<?php

declare(strict_types=1);

namespace Doctrine\Tests\RST\RefInsideDirective;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

use function sprintf;
use function strip_tags;

class VersionAddedDirective extends SubDirective
{
    public function getName(): string
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
    ): ?Node {
        return $parser->getNodeFactory()->createCallableNode(
            static function () use ($data, $document) {
                $nodeValue = '';

                if ($document !== null) {
                    $nodeValue = $document->render();
                }

                return sprintf(
                    '<div class="versionadded"><p><span class="versionmodified">New in version %s: </span>%s</p></div>',
                    $data,
                    strip_tags($nodeValue, '<a><code>')
                );
            }
        );
    }
}
