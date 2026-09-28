<?php

namespace Tests\Support;

use App\Contracts\RouterClient;
use App\Enums\RouterSuspendMethod;
use App\Mikrotik\RouterHealth;
use Throwable;

/**
 * RouterClient tiruan yang mencatat setiap panggilan dan bisa disuruh gagal.
 *
 * Dipakai supaya alur suspend/reaktivasi bisa diuji tanpa router sungguhan,
 * tanpa membuat implementasi RouterClient di app/ menjadi test-only.
 */
final class FakeRouterClient implements RouterClient
{
    /**
     * @var list<array{method: string, action: string, username: string, address: string|null}>
     */
    public array $calls = [];

    public bool $reachable = true;

    public int $sessionCount = 0;

    /**
     * Exception yang dilempar untuk semua operasi, apa pun metodenya.
     */
    public ?Throwable $failEverything = null;

    /**
     * Exception per metode, supaya perilaku fallback bisa diuji: address-list
     * boleh gagal sementara ppp_secret tetap berhasil.
     *
     * @var array<string, Throwable>
     */
    public array $failOnMethod = [];

    /**
     * Setel supaya operasi dengan metode tertentu gagal, metode lain tetap jalan.
     */
    public function failOn(RouterSuspendMethod $method, Throwable $throwable): self
    {
        $this->failOnMethod[$method->value] = $throwable;

        return $this;
    }

    public function health(): RouterHealth
    {
        return $this->reachable
            ? RouterHealth::online('fake-router', '7.15 (stable)', '1w2d3h4m')
            : RouterHealth::offline('Connection refused');
    }

    public function suspend(RouterSuspendMethod $method, string $username, ?string $address = null): void
    {
        $this->record('suspend', $method, $username, $address);
    }

    public function reactivate(RouterSuspendMethod $method, string $username, ?string $address = null): void
    {
        $this->record('reactivate', $method, $username, $address);
    }

    public function activeSessionCount(): int
    {
        return $this->sessionCount;
    }

    /**
     * @return list<array{method: string, action: string, username: string, address: string|null}>
     */
    public function callsFor(string $action): array
    {
        return array_values(array_filter(
            $this->calls,
            static fn (array $call): bool => $call['action'] === $action,
        ));
    }

    private function record(string $action, RouterSuspendMethod $method, string $username, ?string $address): void
    {
        $this->calls[] = [
            'method' => $method->value,
            'action' => $action,
            'username' => $username,
            'address' => $address,
        ];

        $throwable = $this->failOnMethod[$method->value] ?? $this->failEverything;

        if ($throwable !== null) {
            throw $throwable;
        }
    }
}
