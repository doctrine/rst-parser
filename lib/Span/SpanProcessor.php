<?php

declare(strict_types=1);

namespace Doctrine\RST\Span;

use Doctrine\RST\Environment;

use function mt_getrandmax;
use function preg_replace;
use function preg_replace_callback;
use function random_int;
use function sha1;
use function str_replace;
use function time;

final class SpanProcessor
{
    private Environment $environment;

    private string $span;

    private int $tokenId;

    private string $prefix;

    /** @var SpanToken[] */
    private array $tokens = [];

    /** @param  SpanToken[] $tokens */
    public function __construct(Environment $environment, string $span, array $tokens = [])
    {
        $this->environment = $environment;
        $this->span        = $span;
        $this->tokens      = $tokens;
        $this->tokenId     = 0;
        $this->prefix      = random_int(0, mt_getrandmax()) . '|' . time();
    }

    public function process(): string
    {
        $specialTextRoles = $this->environment->getSpecialTextRoles();
        $span             = $this->span;
        foreach ($specialTextRoles as $textRole) {
            $span = $textRole->replaceAndRegisterTokens($this, $span);
        }

        $span = $this->replaceTextRoles($span);

        $span = $this->replaceEscapes($span);

        return $span;
    }

    public function processRecursiveRoles(): string
    {
        $recursiveTextRoles = $this->environment->getRecursiveTextRoles();
        $span               = $this->span;
        foreach ($recursiveTextRoles as $textRole) {
            $span = $textRole->replaceAndRegisterTokens($this, $span);
        }

        return $span;
    }

    public function getEnvironment(): Environment
    {
        return $this->environment;
    }

    /** @return SpanToken[] */
    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function getText(string $value): string
    {
        foreach ($this->tokens as $token) {
            $value = str_replace($token->getId(), $token->get('text'), $value);
        }

        return $value;
    }

    public function addToken(SpanToken $token): void
    {
        $this->tokens[$token->getId()] = $token;
    }

    private function replaceTextRoles(string $span): string
    {
        return (string) preg_replace_callback('/:([a-z0-9]+):`(.+)`/mUsi', function ($match): string {
            $textRoleName = $match[1];

            $text = $match[2];
            $id   = $this->generateId();

            $textRole =  $this->environment->getTextRole($textRoleName);
            $data     = $textRole->process($this->environment, $text);

            $this->addToken(new SpanToken($textRole, $id, $data));

            return $id;
        }, $span);
    }

    /**
     * Removes every backslash that is not escaped by a preceding backslash.
     */
    private function replaceEscapes(string $span): string
    {
        return preg_replace('/(?<!\\\\)\\\\/', '', $span);
    }

    public function generateId(): string
    {
        $this->tokenId++;

        return sha1($this->prefix . '|' . $this->tokenId);
    }
}
