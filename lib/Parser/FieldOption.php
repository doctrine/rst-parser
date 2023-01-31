<?php

declare(strict_types=1);

namespace Doctrine\RST\Parser;

use Doctrine\RST\Nodes\Node;

use function array_reduce;
use function ltrim;
use function str_replace;
use function strlen;
use function substr;
use function trim;

final class FieldOption
{
    private string $name;

    private int $offset;

    private string $body;

    /**
     * It depends on the context of whether the body of a field option should
     * be interpreted as nodes or plain text. Therefore, we store both and
     * delegate the decision on which to use to the rendering.
     *
     * @var Node[]
     */
    private array $nodes = [];

    private int $lineCount = 0;

    public function __construct(string $name, int $offset, string $body)
    {
        $this->name   = str_replace('\: ', ': ', $name);
        $this->offset = $offset + 1;
        $this->body   = $body;
    }

    public function appendLine(string $line): void
    {
        $trimmedLine = ltrim($line);
        if ($trimmedLine !== '') {
            if (++$this->lineCount === 1) {
                $this->offset = strlen($line) - strlen($trimmedLine);
            }

            $trimmedLine = substr($line, $this->offset);
        }

        $this->body .= "\n" . $trimmedLine;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getBody(): string
    {
        return trim($this->body);
    }

    /** @return Node[] */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /** @param Node[] $nodes */
    public function setNodes(array $nodes): void
    {
        $this->nodes = $nodes;
    }

    public function getNodesAsString(): string
    {
        return trim(array_reduce($this->nodes, static fn (string $contents, Node $node): string => $contents . $node->render() . "\n", ''));
    }
}
