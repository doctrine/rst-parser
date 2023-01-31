<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Parser\ListItem;

class ListNode extends Node
{
    private bool $ordered;

    /** @var ListItem[] */
    private array $items;

    /** @param ListItem[] $items */
    public function __construct(array $items, bool $ordered)
    {
        parent::__construct();

        $this->items   = $items;
        $this->ordered = $ordered;
    }

    /** @return ListItem[] */
    public function getItems(): array
    {
        return $this->items;
    }

    public function isOrdered(): bool
    {
        return $this->ordered;
    }
}
