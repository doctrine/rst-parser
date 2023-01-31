<?php

declare(strict_types=1);

namespace Doctrine\RST\Nodes;

use Doctrine\RST\Environment;
use Doctrine\RST\Parser;
use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanToken;

use function implode;
use function is_array;

class SpanNode extends Node
{
    /** @var string */
    protected $value;

    private string $text;

    /** @var Environment */
    protected $environment;

    /** @var SpanToken[] */
    private array $tokens;

    /** @param string|string[]|SpanNode $span */
    public function __construct(Parser $parser, $span)
    {
        parent::__construct();

        $this->environment = $parser->getEnvironment();

        if (is_array($span)) {
            $span = implode("\n", $span);
        }

        if ($span instanceof SpanNode) {
            $span = $span->render();
        }

        $spanProcessor = new SpanProcessor($this->environment, $span);

        $this->value  = $spanProcessor->process();
        $this->text   = $spanProcessor->getText($this->value);
        $this->tokens = $spanProcessor->getTokens();
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /** @return SpanToken[] */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
