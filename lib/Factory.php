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

class Factory
{
    public const HTML  = 'HTML';
    public const LATEX = 'LaTeX';

    /** @var string */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function createEnvironment(?Configuration $configuration = null) : Environment
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Environment($configuration);

            case self::LATEX:
                return new LaTeX\Environment($configuration);
        }
    }

    public function createDocument(Environment $environment) : Document
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Document($environment);

            case self::LATEX:
                return new LaTeX\Document($environment);
        }
    }


    /**
     * @param string[] $files
     * @param string[] $options
     */
    public function createTocNode(Environment $environment, array $files, array $options) : TocNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\TocNode($environment, $files, $options);

            case self::LATEX:
                return new LaTeX\Nodes\TocNode($environment, $files, $options);
        }
    }

    public function createTitleNode(Node $value, int $level, string $token) : TitleNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\TitleNode($value, $level, $token);

            case self::LATEX:
                return new LaTeX\Nodes\TitleNode($value, $level, $token);
        }
    }

    public function createSeparatorNode(int $level) : SeparatorNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\SeparatorNode($level);

            case self::LATEX:
                return new LaTeX\Nodes\SeparatorNode($level);
        }
    }

    /**
     * @param string[] $lines
     */
    public function createCodeNode(array $lines) : CodeNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\CodeNode($lines);

            case self::LATEX:
                return new LaTeX\Nodes\CodeNode($lines);
        }
    }

    /**
     * @param string[] $lines
     */
    public function createQuoteNode(array $lines) : QuoteNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\QuoteNode($lines);

            case self::LATEX:
                return new LaTeX\Nodes\QuoteNode($lines);
        }
    }

    /**
     * @param Node|string|null $value
     */
    public function createParagraphNode($value = null) : ParagraphNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\ParagraphNode($value);

            case self::LATEX:
                return new LaTeX\Nodes\ParagraphNode($value);
        }
    }

    /**
     * @param Node|string|null $value
     */
    public function createAnchorNode($value = null) : AnchorNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\AnchorNode($value);

            case self::LATEX:
                return new LaTeX\Nodes\AnchorNode($value);
        }
    }

    /**
     * @param Node|string|null $value
     */
    public function createListNode($value = null) : ListNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\ListNode($value);

            case self::LATEX:
                return new LaTeX\Nodes\ListNode($value);
        }
    }

    /**
     * @param string[] $parts
     */
    public function createTableNode(array $parts, string $type) : TableNode
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Nodes\TableNode($parts, $type);

            case self::LATEX:
                return new LaTeX\Nodes\TableNode($parts, $type);
        }
    }

    /**
     * @param string|string[]|Span $span
     */
    public function createSpan(Parser $parser, $span) : Span
    {
        switch ($this->name) {
            case self::HTML:
                return new HTML\Span($parser, $span);

            case self::LATEX:
                return new LaTeX\Span($parser, $span);
        }
    }
}
