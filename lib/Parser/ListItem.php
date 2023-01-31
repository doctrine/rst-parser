<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\RST\Nodes\Node;

use function array_reduce;
use function trim;

/**
 * Represents a single item of a bullet or enumerated list.
 */
final class ListItem
{
    /** @var string the list marker used for this item */
    private string $prefix;

    /** @var bool whether the list marker represents an enumerated list */
    private bool $ordered;

    /** @var Node[] */
    private array $contents;

    /** @param Node[] $contents */
    public function __construct(string $prefix, bool $ordered, array $contents)
    {
        $this->prefix   = $prefix;
        $this->ordered  = $ordered;
        $this->contents = $contents;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }

    /** @return Node[] */
    public function getContents(): array
    {
        return $this->contents;
    }

    public function getContentsAsString(): string
    {
        return trim(array_reduce($this->contents, static fn (string $contents, Node $node): string => $contents . $node->render() . "\n", ''));
    }
}
