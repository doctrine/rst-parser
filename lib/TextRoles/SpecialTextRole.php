<?php

declare(strict_types=1);

namespace Doctrine\RST\TextRoles;

use Doctrine\RST\Span\SpanProcessor;
use Doctrine\RST\Span\SpanToken;
use RuntimeException;

use function preg_last_error;
use function preg_last_error_msg;
use function preg_replace_callback;

use const PREG_NO_ERROR;

class SpecialTextRole extends WrapperTextRole
{
    /**
     * Does this text role have a special syntax like ``*cursive*``?
     */
    public function hasSpecialSyntax(): bool
    {
        return true;
    }

    protected function replaceTokens(SpanProcessor $spanProcessor, string $span, string $pattern): string
    {
        $span = (string) preg_replace_callback(
            $pattern,
            function (array $match) use ($spanProcessor): string {
                $id = $spanProcessor->generateId();
                $spanProcessor->addToken(
                    new SpanToken($this, $id, [
                        'type' => $this->getName(),
                        'text' => $match[1],
                    ])
                );

                return $id;
            },
            $span,
        );

        if (preg_last_error() !== PREG_NO_ERROR) {
            throw new RuntimeException(preg_last_error_msg());
        }

        return $span;
    }
}
