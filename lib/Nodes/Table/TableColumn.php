<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes\Table;

use Doctrine\RST\Nodes\Node;
use LogicException;
use function strlen;
use function trim;
use function utf8_encode;

final class TableColumn
{
    /** @var string */
    private $content;

    /** @var int */
    private $colSpan;

    /** @var Node|null */
    private $node;

    public function __construct(string $content, int $colSpan)
    {
        $this->content = utf8_encode(trim($content));
        $this->colSpan = $colSpan;
    }

    public function getContent() : string
    {
        // "\" is a special way to make a column "empty", but
        // still indicate that you *want* that column
        if ($this->content === '\\') {
            return '';
        }

        return $this->content;
    }

    public function getColSpan() : int
    {
        return $this->colSpan;
    }

    public function addContent(string $content) : void
    {
        $this->content = trim($this->content . utf8_encode($content));
    }

    public function getNode() : Node
    {
        if ($this->node === null) {
            throw new LogicException('The node is not yet set.');
        }

        return $this->node;
    }

    public function setNode(Node $node) : void
    {
        $this->node = $node;
    }

    public function render() : string
    {
        return $this->getNode()->render();
    }

    public function isEmpty() : bool
    {
        return strlen($this->content) === 0;
    }
}
