<?php

declare(strict_types=1);

namespace Doctrine\RST\HTML\Directives;

use Doctrine\RST\Directives\SubDirective;
use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\DocumentNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Parser;

use function array_map;
use function explode;

final class ClassDirective extends SubDirective
{
    public function getName(): string
    {
        return 'class';
    }

    /** @param string[] $options */
    public function processSub(
        Parser $parser,
        ?Node $document,
        string $variable,
        string $data,
        array $options
    ): ?Node {
        if ($document === null) {
            return null;
        }

        $classes = explode(' ', $data);

        $normalizedClasses = array_map(static function (string $class): string {
            return Environment::slugify($class);
        }, $classes);

        $document->setClasses($normalizedClasses);

        if ($document instanceof DocumentNode) {
            $this->setNodesClasses($document->getNodes(), $classes);
        }

        return $document;
    }

    public function appliesToNonBlockContent(): bool
    {
        return true;
    }

    /**
     * @param Node[]   $nodes
     * @param string[] $classes
     */
    private function setNodesClasses(array $nodes, array $classes): void
    {
        foreach ($nodes as $node) {
            $node->setClasses($classes);

            if (! ($node instanceof DocumentNode)) {
                continue;
            }

            $this->setNodesClasses($node->getNodes(), $classes);
        }
    }
}
