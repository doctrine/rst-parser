<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

use function count;
use function implode;
use function strlen;
use function substr;
use function trim;

abstract class BlockNode extends Node
{
    public function __construct(array $lines)
    {
        if (count($lines)) {
            $firstLine = $lines[0];
            for ($k=0; $k<strlen($firstLine); $k++) {
                if (trim($firstLine[$k])) {
                    break;
                }
            }

            foreach ($lines as &$line) {
                $line = substr($line, $k);
            }
        }

        $this->value = implode("\n", $lines);
    }
}
