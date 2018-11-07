<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use function array_pop;
use function count;

abstract class ListNode extends Node
{
    /** @var mixed[][] */
    protected $lines = [];

    /**
     * Infos contains:
     *
     * - text: the line text
     * - depth: the depth in the list level
     * - prefix: the prefix char (*, - etc.)
     * - ordered: true of false if the list is ordered
     *
     * @param mixed[] $line
     */
    public function addLine(array $line) : void
    {
        $this->lines[] = $line;
    }

    protected function doRender() : string
    {
        $depth = -1;
        $value = '';
        $stack = [];

        foreach ($this->lines as $line) {
            $prefix   = $line['prefix'];
            $text     = $line['text'];
            $ordered  = $line['ordered'];
            $newDepth = $line['depth'];

            if ($depth < $newDepth) {
                $tags    = $this->createList($ordered);
                $value  .= $tags[0];
                $stack[] = [$newDepth, $tags[1] . "\n"];
                $depth   = $newDepth;
            }

            while ($depth > $newDepth) {
                $top = $stack[count($stack) - 1];

                if ($top[0] <= $newDepth) {
                    continue;
                }

                $value .= $top[1];
                array_pop($stack);
                $top   = $stack[count($stack) - 1];
                $depth = $top[0];
            }

            $value .= $this->createElement((string) $text, $prefix) . "\n";
        }

        while ($stack) {
            [$d, $closing] = array_pop($stack);
            $value        .= $closing;
        }

        return $value;
    }

    abstract protected function createElement(string $text, string $prefix) : string;

    /**
     * @return string[]
     */
    abstract protected function createList(bool $ordered) : array;
}
