<?php

namespace App\Mikrotik;

/**
 * Hasil pemeriksaan konektivitas & identitas router.
 */
final readonly class RouterHealth
{
    public function __construct(
        public bool $reachable,
        public ?string $identity = null,
        public ?string $version = null,
        public ?string $uptime = null,
        public ?string $error = null,
    ) {}

    public static function online(string $identity, ?string $version = null, ?string $uptime = null): self
    {
        return new self(
            reachable: true,
            identity: $identity,
            version: $version,
            uptime: $uptime,
        );
    }

    public static function offline(string $error): self
    {
        return new self(reachable: false, error: $error);
    }
}
