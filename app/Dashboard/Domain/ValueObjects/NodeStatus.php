<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ValueObjects;

final class NodeStatus
{
    public const ONLINE  = 'ONLINE';
    public const SYNCING = 'SYNCING';
    public const HI_LOAD = 'HI-LOAD';
    public const ERROR   = 'ERROR';
    public const OFFLINE = 'OFFLINE';

    private static array $valid = [self::ONLINE, self::SYNCING, self::HI_LOAD, self::ERROR, self::OFFLINE];

    private readonly string $value;

    public function __construct(string $value)
    {
        $upper = strtoupper(trim($value));
        $this->value = in_array($upper, [self::ONLINE, self::SYNCING, self::HI_LOAD, self::ERROR, self::OFFLINE], true)
            ? $upper
            : self::OFFLINE;
    }

    public static function online(): self  { return new self(self::ONLINE); }
    public static function syncing(): self { return new self(self::SYNCING); }
    public static function hiLoad(): self  { return new self(self::HI_LOAD); }
    public static function error(): self   { return new self(self::ERROR); }
    public static function offline(): self { return new self(self::OFFLINE); }

    public function value(): string       { return $this->value; }
    public function isOnline(): bool      { return $this->value === self::ONLINE; }
    public function isHealthy(): bool     { return in_array($this->value, [self::ONLINE, self::SYNCING], true); }
    public function __toString(): string  { return $this->value; }
}
