<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Nodes\AnchorNode;
use Doctrine\RST\Nodes\CodeNode;
use Doctrine\RST\Nodes\ListNode;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\ParagraphNode;
use Doctrine\RST\Nodes\QuoteNode;
use Doctrine\RST\Nodes\SeparatorNode;
use Doctrine\RST\Nodes\TableNode;
use Doctrine\RST\Nodes\TitleNode;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Parser\LineChecker;

interface NodeFactory
{
    public function createDocument(Environment $environment) : Document;

    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function createToc(Environment $environment, array $files, array $options) : TocNode;

    public function createTitle(Node $value, int $level, string $token) : TitleNode;

    public function createSeparator(int $level) : SeparatorNode;

    /**
     * @param string[] $lines
     */
    public function createCode(array $lines) : CodeNode;

    /**
     * @param string[] $lines
     */
    public function createQuote(array $lines) : QuoteNode;

    /**
     * @param Node|string|null $value
     */
    public function createParagraph($value = null) : ParagraphNode;

    /**
     * @param Node|string|null $value
     */
    public function createAnchor($value = null) : AnchorNode;

    /**
     * @param Node|string|null $value
     */
    public function createList($value = null) : ListNode;

    /**
     * @param string[] $parts
     */
    public function createTable(array $parts, string $type, LineChecker $lineChecker) : TableNode;

    /**
     * @param string|string[]|Span $span
     */
    public function createSpan(Parser $parser, $span) : Span;
}
