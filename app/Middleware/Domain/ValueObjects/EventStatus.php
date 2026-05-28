<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ValueObjects;

final class EventStatus
{
    public const PENDING   = 'PENDING';
    public const PROCESADO = 'PROCESADO';
    public const FALLIDO   = 'FALLIDO';

    private readonly string $value;

    public function __construct(string $value)
    {
        $upper = strtoupper(trim($value));
        $this->value = in_array($upper, [self::PENDING, self::PROCESADO, self::FALLIDO], true)
            ? $upper
            : self::PENDING;
    }

    public static function pending(): self   { return new self(self::PENDING); }
    public static function procesado(): self { return new self(self::PROCESADO); }
    public static function fallido(): self   { return new self(self::FALLIDO); }

    public function isPending(): bool   { return $this->value === self::PENDING; }
    public function isProcessed(): bool { return $this->value === self::PROCESADO; }
    public function isFailed(): bool    { return $this->value === self::FALLIDO; }
    public function value(): string     { return $this->value; }
    public function __toString(): string { return $this->value; }
}
