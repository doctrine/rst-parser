<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\RST\Nodes\TableNode;
use function count;
use function strlen;
use function trim;

class TableParser
{
    private const TABLE_LETTER = '=';

    private const PRETTY_TABLE_LETTER = '-';

    private const PRETTY_TABLE_HEADER = '=';

    private const PRETTY_TABLE_JOINT = '+';

    /**
     * @return mixed[]|null
     */
    public function parseTableLine(string $line) : ?array
    {
        $header = false;
        $pretty = false;
        $line   = trim($line);

        if ($line === '') {
            return null;
        }

        // Finds the table chars
        $chars = $this->findTableChars($line);

        if ($chars === null) {
            return null;
        }

        if ($chars[0] === self::PRETTY_TABLE_JOINT && $chars[1] === self::PRETTY_TABLE_LETTER) {
            $pretty = true;
            $chars  = [self::PRETTY_TABLE_LETTER, self::PRETTY_TABLE_JOINT];
        } elseif ($chars[0] === self::PRETTY_TABLE_JOINT && $chars[1] === self::PRETTY_TABLE_HEADER) {
            $pretty = true;
            $header = true;
            $chars  = [self::PRETTY_TABLE_HEADER, self::PRETTY_TABLE_JOINT];
        } else {
            if (! ($chars[0] === self::TABLE_LETTER && $chars[1] === ' ')) {
                return null;
            }
        }

        $parts     = [];
        $separator = false;

        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] === $chars[0]) {
                if (! $separator) {
                    $parts[]   = $i;
                    $separator = true;
                }
            } else {
                if ($line[$i] !== $chars[1]) {
                    return null;
                }

                $separator = false;
            }
        }

        if (count($parts) > 1) {
            return [
                $header,
                $pretty,
                $parts,
            ];
        }

        return null;
    }

    public function guessTableType(string $line) : string
    {
        return $line[0] === self::TABLE_LETTER ? TableNode::TYPE_SIMPLE : TableNode::TYPE_PRETTY;
    }

    /**
     * @return string[]
     */
    private function findTableChars(string $line) : ?array
    {
        $lineChar  = $line[0];
        $spaceChar = null;

        for ($i = 0; $i < strlen($line); $i++) {
            if ($line[$i] === $lineChar) {
                continue;
            }

            if ($spaceChar === null) {
                $spaceChar = $line[$i];
            } else {
                if ($line[$i] !== $spaceChar) {
                    return null;
                }
            }
        }

        return [$lineChar, $spaceChar];
    }
}
