<?php

declare(strict_types=1);

namespace Doctrine\RST\Span;

use Doctrine\RST\TextRoles\TextRole;

final class SpanToken
{
    public const TYPE_LITERAL     = 'literal';
    public const TYPE_INTERPRETED = 'interpreted';
    public const TYPE_TEXT_ROLE   = 'textrole';
    public const TYPE_UNKOWN      = 'textrole';
    public const TYPE_LINK        = 'link';

    private string $type;
    private string $id;
    private ?TextRole $textRole;

    /** @var string[] */
    private array $token;

    /** @param string[] $token */
    public function __construct(?TextRole $textRole, string $id, array $token)
    {
        $this->textRole      = $textRole;
        $this->type          = $textRole ? $textRole->getName() : self::TYPE_UNKOWN;
        $this->id            = $id;
        $this->token         = $token;
        $this->token['type'] = $this->type;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTextRole(): ?TextRole
    {
        return $this->textRole;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function get(string $key): string
    {
        return $this->token[$key] ?? '';
    }

    /** @return string[] */
    public function getTokenData(): array
    {
        return $this->token;
    }
}
