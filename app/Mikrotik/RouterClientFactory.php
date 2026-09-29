<?php

namespace App\Mikrotik;

use App\Contracts\RouterClient;
use App\Models\Router;

/**
 * Membangun RouterClient dari data router yang tersimpan.
 *
 * Titik tunggal ini yang menyentuh library RouterOS, sehingga penggantiannya
 * di masa depan cukup dilakukan di satu tempat.
 */
class RouterClientFactory
{
    public function make(Router $router): RouterClient
    {
        return new RouterOsClient(
            routerName: $router->name,
            host: $router->vpn_ip,
            username: $router->username,
            password: $router->password,
            port: $router->api_port ?: (int) config('mikrotik.default_port'),
            useSsl: $router->use_ssl,
            blockListName: $router->blocklistName(),
        );
    }
}
