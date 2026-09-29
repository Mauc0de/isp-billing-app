<?php

namespace App\Suspension;

use App\Enums\RouterStatus;

/**
 * Ringkasan hasil satu putaran pemeriksaan kesehatan router.
 */
final readonly class RouterSyncReport
{
    /**
     * @param  list<RouterStatus>  $statuses
     */
    public function __construct(
        public int $tenants,
        public array $statuses,
    ) {}

    public static function empty(): self
    {
        return new self(tenants: 0, statuses: []);
    }

    public function checked(): int
    {
        return count($this->statuses);
    }

    public function online(): int
    {
        return count(array_filter($this->statuses, static fn (RouterStatus $status): bool => $status === RouterStatus::Online));
    }

    public function offline(): int
    {
        return count(array_filter($this->statuses, static fn (RouterStatus $status): bool => $status === RouterStatus::Offline));
    }

    public function foundNothing(): bool
    {
        return $this->statuses === [];
    }

    public function summary(): string
    {
        return sprintf(
            '%d router diperiksa pada %d tenant: %d online, %d offline.',
            $this->checked(),
            $this->tenants,
            $this->online(),
            $this->offline(),
        );
    }
}
