<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Operasi ke router gagal: tidak bisa.connect, kredensial ditolak, atau
 * RouterOS membalas dengan !trap/!fatal.
 */
class RouterOperationFailed extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $routerName = null,
        public readonly ?string $operation = null,
        public readonly bool $addressUnavailable = false,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public static function connectionFailed(string $routerName, string $host, int $port, ?Throwable $previous = null): self
    {
        return new self(
            "Tidak bisa menghubungi router {$routerName} di {$host}:{$port}.",
            $routerName,
            'connect',
            false,
            $previous,
        );
    }

    public static function operationFailed(string $routerName, string $operation, string $reason): self
    {
        return new self(
            "RouterOS menolak operasi {$operation} di {$routerName}: {$reason}",
            $routerName,
            $operation,
        );
    }

    /**
     * Pelanggan tidak punya IP Address yang bisa diblokir, jadi metode
     * address-list tidak mungkin dipakai. Pemanggil boleh mencoba metode lain.
     */
    public static function addressUnavailable(string $routerName, string $username): self
    {
        return new self(
            "Tidak ada IP Address untuk {$username} di router {$routerName}, sehingga tidak bisa diblokir via address-list.",
            $routerName,
            'address_list',
            true,
        );
    }
}
