<?php

declare(strict_types=1);

namespace Doctrine\RST\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\Node;
use Doctrine\RST\Nodes\SpanNode;
use Doctrine\RST\References\ResolvedReference;
use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanToken;

use function is_string;
use function preg_replace_callback;
use function str_replace;

abstract class SpanNodeRenderer implements NodeRenderer, SpanRenderer
{
    public const MAX_TOKEN_ITERATION = 10;
    /** @var Environment */
    protected $environment;

    /** @var SpanNode */
    protected $spanNode;

    public function __construct(
        Environment $environment,
        SpanNode $spanNode
    ) {
        $this->environment = $environment;
        $this->spanNode    = $spanNode;
    }

    public function render(): string
    {
        $value = $this->spanNode->getValue();

        $span = $this->processSyntax($value);

        $span = $this->renderTokens($span);

        return $span;
    }

    private function processSyntax(string $span): string
    {
        $spanProcessor = new SpanProcessor($this->environment, $span, $this->spanNode->getTokens());
        $span          = $spanProcessor->processRecursiveRoles();
        $this->spanNode->setTokens($spanProcessor->getTokens());
        $this->spanNode->setValue($span);

        $span = $this->escape($span);

        $span = $this->renderVariables($span);

        return $span;
    }

    private function renderVariables(string $span): string
    {
        return (string) preg_replace_callback('/\|(.+)\|/mUsi', function (array $match): string {
            $variable = $this->environment->getVariable($match[1]);

            if ($variable === null) {
                return '';
            }

            if ($variable instanceof Node) {
                return $variable->render();
            }

            if (is_string($variable)) {
                return $variable;
            }

            return (string) $variable;
        }, $span);
    }

    private function renderTokens(string $span): string
    {
        $previousSpan = '';
        $i            = 0;
        while ($previousSpan !== $span && $i < self::MAX_TOKEN_ITERATION) {
            $previousSpan = $span;
            foreach ($this->spanNode->getTokens() as $token) {
                $span = $this->renderToken($token, $span);
            }

            $i++;
        }

        return $span;
    }

    private function renderToken(SpanToken $spanToken, string $span): string
    {
        if ($spanToken->getType() === SpanToken::TYPE_LINK) {
            $spanToken->set('linktext', $this->processSyntax($spanToken->get('link')));
        }

        $textRole = $spanToken->getTextRole();

        if ($textRole === null) {
            return $spanToken->get('text');
        }

        $resolvedTextRole = $textRole->render($this->environment, $spanToken);

        return str_replace($spanToken->getId(), $resolvedTextRole, $span);
    }

    /** @param mixed[] $attributes */
    public function link(?string $url, string $title, array $attributes = []): string
    {
        return $this->environment->getLinkRenderer()->renderUrl($url, $title, $attributes);
    }

    /** @param mixed[] $value */
    public function reference(ResolvedReference $reference, array $value): string
    {
        return $this->environment->getLinkRenderer()->renderReference($reference, $value);
    }
}
