<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Parser\FieldOption;

class FieldListNode extends Node
{
    /** @var FieldOption[] */
    private array $items;

    /** @param FieldOption[] $items */
    public function __construct(array $items)
    {
        parent::__construct();

        $this->items = $items;
    }

    /** @return FieldOption[] */
    public function getItems(): array
    {
        return $this->items;
    }
}
