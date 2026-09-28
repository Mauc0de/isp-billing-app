<?php

namespace App\Mikrotik;

use App\Contracts\RouterClient;
use App\Enums\RouterSuspendMethod;
use App\Exceptions\RouterOperationFailed;
use RouterOS\Client as RouterOsConnection;
use RouterOS\Exceptions\ClientException;
use RouterOS\Exceptions\ConfigException;
use RouterOS\Exceptions\QueryException;
use RouterOS\Exceptions\StreamException;
use RouterOS\Query;
use Throwable;

/**
 * Implementasi RouterClient di atas evilfreelancer/routeros-api-php.
 *
 * Catatan penting soal library: closeSocket() bersifat private, jadi objek
 * Client tidak bisa dipakai ulang setelah selesai. Karena itu setiap operasi
 * membuka koneksi baru dan objeknya dibiarkan di-GC. Pola ini memang yang
 * paling wajar dan sesuai untuk job queue yang sekali jalan.
 */
final class RouterOsClient implements RouterClient
{
    public function __construct(
        private readonly string $routerName,
        private readonly string $host,
        private readonly string $username,
        private readonly string $password,
        private readonly int $port,
        private readonly bool $useSsl,
        private readonly string $blockListName,
    ) {}

    public function health(): RouterHealth
    {
        try {
            $resource = $this->run(new Query((string) config('mikrotik.endpoints.resource')));
        } catch (RouterOperationFailed $exception) {
            return RouterHealth::offline($exception->getMessage());
        }

        $row = $resource['rows'][0] ?? [];

        // Endpoint identity butuh policy API yang lebih ketat. Kalau router
        // menolaknya, board-name dari /system/resource sudah cukup informatif.
        $identity = $row['board-name'] ?? null;

        try {
            $named = $this->run(new Query((string) config('mikrotik.endpoints.identity')));
            $identity = $named['rows'][0]['name'] ?? $identity;
        } catch (RouterOperationFailed) {
            // Diabaikan, lihat catatan di atas.
        }

        return RouterHealth::online(
            identity: $identity ?? $this->routerName,
            version: $row['version'] ?? null,
            uptime: $row['uptime'] ?? null,
        );
    }

    public function suspend(RouterSuspendMethod $method, string $username, ?string $address = null): void
    {
        match ($method) {
            RouterSuspendMethod::PppSecret => $this->setPppSecretDisabled($username, disabled: true),
            RouterSuspendMethod::AddressList => $this->addToBlockList($username, $address),
        };
    }

    public function reactivate(RouterSuspendMethod $method, string $username, ?string $address = null): void
    {
        match ($method) {
            RouterSuspendMethod::PppSecret => $this->setPppSecretDisabled($username, disabled: false),
            RouterSuspendMethod::AddressList => $this->removeFromBlockList($username, $address),
        };
    }

    public function activeSessionCount(): int
    {
        return count($this->run(new Query((string) config('mikrotik.endpoints.active')))['rows']);
    }

    /**
     * Aktifkan/nonaktifkan PPP secret. Idempoten: kalau sudah berada di state
     * yang diminta, tidak ada perintah yang dikirim ke router.
     */
    private function setPppSecretDisabled(string $username, bool $disabled): void
    {
        $secret = $this->findPppSecret($username);
        $current = ($secret['disabled'] ?? 'false') === 'true';

        if ($current === $disabled) {
            return;
        }

        $this->run(new Query(
            config('mikrotik.endpoints.ppp_secret').'/set',
            ['?name='.$username, '=disabled='.($disabled ? 'yes' : 'no')],
        ));
    }

    private function addToBlockList(string $username, ?string $address): void
    {
        $address = $this->resolveAddress($username, $address);

        // Buang entri lama dulu supaya aman dipanggil berulang kali.
        $this->removeFromBlockList($username, $address, silent: true);

        $this->run(new Query(
            config('mikrotik.endpoints.address_list').'/add',
            [
                '=list='.$this->blockListName,
                '=address='.$address,
                '=comment=satak:'.$username,
            ],
        ));
    }

    private function removeFromBlockList(string $username, ?string $address, bool $silent = false): void
    {
        if ($address === null) {
            return;
        }

        $query = new Query(
            config('mikrotik.endpoints.address_list').'/remove',
            ['?list='.$this->blockListName, '?address='.$address],
        );

        if ($silent) {
            // Entri yang memang tidak ada akan membalas !trap. Untuk
            // removeFromBlockList itu hasil yang diharapkan, bukan error.
            try {
                $this->run($query);
            } catch (RouterOperationFailed) {
                // Diabaikan dengan sengaja.
            }

            return;
        }

        $this->run($query);
    }

    /**
     * Tentukan IP Address yang akan diblokir.
     *
     * Kalau pelanggan tidak punya IP statis di data kita, ambil dari
     * remote-address PPP secret miliknya. Pelanggan yang sedang disconnect
     * tidak punya remote-address, sehingga pemanggil wajib menangani kasus itu.
     */
    private function resolveAddress(string $username, ?string $address): string
    {
        $address ??= $this->findPppSecret($username)['remote-address'] ?? null;

        if ($address === null || trim($address) === '') {
            throw RouterOperationFailed::addressUnavailable($this->routerName, $username);
        }

        // Buang sufiks CIDR, mis. 10.0.0.5/32 -> 10.0.0.5
        return explode('/', trim($address))[0];
    }

    /**
     * @return array<string, string>
     */
    private function findPppSecret(string $username): array
    {
        $result = $this->run(new Query(
            (string) config('mikrotik.endpoints.ppp_secret').'/print',
            ['?name='.$username],
        ));

        $secret = $result['rows'][0] ?? null;

        if ($secret === null) {
            throw new RouterOperationFailed(
                "PPP secret '{$username}' tidak ditemukan di router {$this->routerName}.",
                $this->routerName,
                'ppp_secret/print',
            );
        }

        return $secret;
    }

    /**
     * Jalankan satu query dan kembalikan baris hasil + error RouterOS.
     *
     * Respons RouterOS dibaca mentah (read(false)) supaya blok !trap dan !fatal
     * bisa dibedakan dari baris data biasa. Parser bawaan library mencampur
     * keduanya pada kasus multi-row, yang menyulitkan deteksi kegagalan.
     *
     * @return array{rows: list<array<string, string>>, errors: list<array<string, string>>}
     */
    private function run(Query $query): array
    {
        $connection = $this->connect();

        try {
            $raw = $connection->query($query)->read(false);
        } catch (ClientException|StreamException $exception) {
            throw new RouterOperationFailed(
                "Koneksi ke router {$this->routerName} terputus saat membaca respons: {$exception->getMessage()}",
                $this->routerName,
                $query->getEndpoint() ?? 'unknown',
                $exception,
            );
        }

        $parsed = $this->parse($raw);

        if ($parsed['errors'] !== []) {
            $first = $parsed['errors'][0];

            throw RouterOperationFailed::operationFailed(
                $this->routerName,
                $query->getEndpoint() ?? 'unknown',
                $first['message'] ?? 'tanpa keterangan',
            );
        }

        return $parsed;
    }

    private function connect(): RouterOsConnection
    {
        try {
            return new RouterOsConnection([
                'host' => $this->host,
                'user' => $this->username,
                'pass' => $this->password,
                'port' => $this->port,
                'ssl' => $this->useSsl,
                'timeout' => (int) config('mikrotik.connect_timeout'),
                'socket_timeout' => (int) config('mikrotik.read_timeout'),
                'attempts' => (int) config('mikrotik.attempts'),
                'delay' => (int) config('mikrotik.retry_delay'),
                'legacy' => (bool) config('mikrotik.legacy_login'),
            ]);
        } catch (ClientException|ConfigException|QueryException|StreamException $exception) {
            throw RouterOperationFailed::connectionFailed(
                $this->routerName,
                $this->host,
                $this->port,
                $exception,
            );
        } catch (Throwable $exception) {
            throw RouterOperationFailed::connectionFailed(
                $this->routerName,
                $this->host,
                $this->port,
                $exception,
            );
        }
    }

    /**
     * @param  list<string>  $raw
     * @return array{rows: list<array<string, string>>, errors: list<array<string, string>>}
     */
    private function parse(array $raw): array
    {
        $rows = [];
        $errors = [];
        $block = null;
        $kind = null;

        foreach ($raw as $word) {
            if ($word === '!re' || $word === '!trap' || $word === '!fatal') {
                $this->flush($block, $kind, $rows, $errors);

                $kind = $word;
                $block = [];

                continue;
            }

            if ($word === '!done') {
                $this->flush($block, $kind, $rows, $errors);

                break;
            }

            if ($block === null) {
                continue;
            }

            if (preg_match('/^[=.]([^=]+)=(.*)$/s', $word, $matches) === 1) {
                $block[$matches[1]] = $matches[2];
            }
        }

        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * @param  array<string, string>|null  $block
     * @param  list<array<string, string>>  $rows
     * @param  list<array<string, string>>  $errors
     */
    private function flush(?array $block, ?string $kind, array &$rows, array &$errors): void
    {
        if ($block === null || $block === []) {
            return;
        }

        if ($kind === '!re') {
            $rows[] = $block;

            return;
        }

        $errors[] = $block;
    }
}
