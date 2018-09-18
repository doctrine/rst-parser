<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Parser;
use Doctrine\RST\Span;
use RuntimeException;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function count;
use function implode;
use function sprintf;
use function strlen;
use function substr;
use function trim;
use function utf8_decode;
use function utf8_encode;

abstract class TableNode extends Node
{
    public const TYPE_SIMPLE = 'simple';
    public const TYPE_PRETTY = 'pretty';

    /** @var mixed[] */
    protected $parts = [];

    /** @var string[][]|Span[][] */
    protected $data = [];

    /** @var bool[] */
    protected $headers = [];

    /** @var string */
    protected $type;

    /**
     * @param string[] $parts
     */
    public function __construct(array $parts, string $type)
    {
        parent::__construct();

        $this->parts  = $parts;
        $this->data[] = [];
        $this->type   = $type;
    }

    public function getCols() : int
    {
        return count($this->parts[2]);
    }

    public function getRows() : int
    {
        return count($this->data) - 1;
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

            if ($this->type === self::TYPE_PRETTY) {
                if ($parts[0] === true) {
                    $this->headers[count($this->data) - 1] = true;
                }
                $this->data[] = [];
            } elseif (count($this->headers) === 0) {
                $this->headers = array_fill_keys(array_keys($this->data), true);
            }
        } else {
            if ($this->type === self::TYPE_SIMPLE) {
                $this->data[] = [];
            }

            // Pushing data in the cells
            [$header, $pretty, $parts] = $this->parts;

            $row = &$this->data[count($this->data) - 1];

            for ($k = 1; $k <= count($parts); $k++) {
                if (strlen($line) >= $parts[$k - 1]) {
                    if ($k === count($parts)) {
                        $data = substr($line, $parts[$k - 1]);
                    } else {
                        $data = substr($line, $parts[$k - 1], $parts[$k] - $parts[$k - 1]);
                    }

                    if ($pretty) {
                        $data = substr($data, 0, -1);
                    }

                    $data = utf8_encode(trim($data));
                } else {
                    $data = '';
                }

                if (isset($row[$k - 1])) {
                    $row[$k - 1] .= ' ' . $data;
                } else {
                    $row[$k - 1] = $data;
                }
            }
        }

        return true;
    }

    public function finalize(Parser $parser) : void
    {
        if (count($this->headers) === count($this->data)) {
            $data = array_map(static function ($item) {
                return implode(' | ', $item);
            }, $this->data);

            throw new RuntimeException(sprintf("Malformed table:\n%s\n\nin file: \"%s\"", implode("\n", $data), $parser->getFilename()));
        }

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
