<?php

namespace Tests\Support;

use App\Contracts\RouterClient;
use App\Mikrotik\RouterClientFactory;
use App\Models\Router;

/**
 * Factory yang selalu mengembalikan FakeRouterClient yang sama, supaya test bisa
 * memeriksa daftar panggilan tanpa membuka socket.
 */
final class FakeRouterClientFactory extends RouterClientFactory
{
    public function __construct(private readonly FakeRouterClient $client) {}

    public function make(Router $router): RouterClient
    {
        return $this->client;
    }
}
