<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Parser;
use Doctrine\RST\Span;
use function count;
use function substr;
use function trim;
use function utf8_decode;
use function utf8_encode;

abstract class TableNode extends Node
{
    /** @var mixed[] */
    protected $parts = [];

    /** @var string[][]|Span[][] */
    protected $data = [];

    /** @var bool[] */
    protected $headers = [];

    /**
     * @param string[] $parts
     */
    public function __construct(array $parts)
    {
        parent::__construct();

        $this->parts  = $parts;
        $this->data[] = [];
    }

    public function getCols() : int
    {
        return count($this->parts[2]);
    }

    public function getRows() : int
    {
        return count($this->data)-1;
    }

    /**
     * @param mixed[]|null $parts
     */
    public function push(?array $parts, string $line) : bool
    {
        $line = utf8_decode($line);

        if ($parts !== null) {
            // New line in the tab
            if ($parts[2] !== $this->parts[2]) {
                return false;
            }

            if ($parts[0] === true) {
                $this->headers[count($this->data) - 1] = true;
            }
            $this->data[] = [];
        } else {
            // Pushing data in the cells
            list($header, $pretty, $parts) = $this->parts;

            $row = &$this->data[count($this->data)-1];

            for ($k = 1; $k <= count($parts); $k++) {
                if ($k === count($parts)) {
                    $data = substr($line, $parts[$k-1]);
                } else {
                    $data = substr($line, $parts[$k-1], $parts[$k]-$parts[$k-1]);
                }

                if ($pretty) {
                    $data = substr($data, 0, -1);
                }

                $data = is_string($data) ? utf8_encode(trim($data)) : '';

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
            if ($row === []) {
                continue;
            }

            foreach ($row as &$col) {
                $col = $parser->createSpan($col);
            }
        }
    }
}
