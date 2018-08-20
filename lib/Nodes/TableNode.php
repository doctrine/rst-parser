<?php

declare(strict_types=1);

namespace Gregwar\RST\Nodes;

use Gregwar\RST\Parser;
use function count;
use function substr;
use function trim;
use function utf8_decode;
use function utf8_encode;

abstract class TableNode extends Node
{
    protected $parts;
    protected $data    = [];
    protected $headers = [];

    public function __construct($parts)
    {
        $this->parts  = $parts;
        $this->data[] = [];
    }

    /**
     * Gets the columns count of the table
     */
    public function getCols()
    {
        return count($this->parts[2]);
    }

    /**
     * Gets the rows count of the table
     */
    public function getRows()
    {
        return count($this->data)-1;
    }

    public function push($parts, $line)
    {
        $line = utf8_decode($line);

        if ($parts) {
            // New line in the tab
            if ($parts[2] !== $this->parts[2]) {
                return false;
            }

            if ($parts[0]) {
                $this->headers[count($this->data)-1] = true;
            }
            $this->data[] = [];
        } else {
            // Pushing data in the cells
            list($header, $pretty, $parts) = $this->parts;
            $row                           = &$this->data[count($this->data)-1];

            for ($k=1; $k<=count($parts); $k++) {
                if ($k === count($parts)) {
                    $data = substr($line, $parts[$k-1]);
                } else {
                    $data = substr($line, $parts[$k-1], $parts[$k]-$parts[$k-1]);
                }

                if ($pretty) {
                    $data = substr($data, 0, -1);
                }

                $data = utf8_encode(trim($data));

                if (isset($row[$k-1])) {
                    $row[$k-1] .= ' ' . $data;
                } else {
                    $row[$k-1] = $data;
                }
            }
        }

        return true;
    }

    public function finalize(Parser $parser) : void
    {
        foreach ($this->data as &$row) {
            if (! $row) {
                continue;
            }

            foreach ($row as &$col) {
                $col = $parser->createSpan($col);
            }
        }
    }
}
