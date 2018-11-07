<?php

declare(strict_types=1);

namespace Doctrine\RST;

use Doctrine\RST\Nodes\Node;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanRenderer;
use function assert;
use function implode;
use function is_array;
use function is_string;

abstract class Span extends Node
{
    /** @var Parser */
    protected $parser;

    /** @var SpanRenderer */
    protected $spanRenderer;

    /** @var Environment */
    protected $environment;

    /**
     * @param string|string[]|Span $span
     */
    public function __construct(Parser $parser, $span)
    {
        parent::__construct();

        $this->parser      = $parser;
        $this->environment = $parser->getEnvironment();

        if (is_array($span)) {
            $span = implode("\n", $span);
        }

        if ($span instanceof Span) {
            $span = (string) $span;
        }

        $spanProcessor = new SpanProcessor($this->environment, $span);

        $processedSpan = $spanProcessor->process();
        $tokens        = $spanProcessor->getTokens();

        $this->spanRenderer = new SpanRenderer(
            $this->environment,
            $this,
            $processedSpan,
            $tokens
        );
    }

    public function doRender() : string
    {
        return $this->spanRenderer->render();
    }

    public function emphasis(string $text) : string
    {
        return $text;
    }

    public function strongEmphasis(string $text) : string
    {
        return $text;
    }

    public function nbsp() : string
    {
        return ' ';
    }

    public function br() : string
    {
        return "\n";
    }

    public function literal(string $text) : string
    {
        return $text;
    }

    public function link(string $url, string $title) : string
    {
        return $title . ' (' . $url . ')';
    }

    public function escape(string $span) : string
    {
        return $span;
    }

    /**
     * @param string[] $value
     */
    public function reference(ResolvedReference $reference, array $value) : string
    {
        $url  = $reference->getUrl();
        $text = $value['text'] ?: $reference->getTitle();

        assert(is_string($url));
        assert(is_string($text));

        return $this->link($url, $text);
    }
}
