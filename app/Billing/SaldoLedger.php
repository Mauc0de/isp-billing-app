<?php

namespace App\Billing;

use App\Enums\SaldoMutationType;
use App\Models\Pelanggan;
use App\Models\SaldoMutation;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Buku besar saldo pelanggan.
 *
 * Semua perubahan saldo HARUS lewat kelas ini agar:
 *  - kolom pelanggans.saldo (sumber kebenaran) dan riwayat saldo_mutations
 *    selalu konsisten,
 *  - saldo tidak pernah negatif,
 *  - baris di-lock saat debit sehingga dua proses paralel tidak bisa
 *    membuat saldo minus (race condition).
 */
class SaldoLedger
{
    public function credit(
        Pelanggan $pelanggan,
        int $jumlah,
        string $sumber,
        ?string $referensi = null,
        ?string $keterangan = null,
    ): SaldoMutation {
        if ($jumlah <= 0) {
            throw new LogicException('Jumlah kredit saldo harus lebih dari nol.');
        }

        return $this->apply($pelanggan, SaldoMutationType::Kredit, $jumlah, $sumber, $referensi, $keterangan);
    }

    public function debit(
        Pelanggan $pelanggan,
        int $jumlah,
        string $sumber,
        ?string $referensi = null,
        ?string $keterangan = null,
    ): SaldoMutation {
        if ($jumlah <= 0) {
            throw new LogicException('Jumlah debit saldo harus lebih dari nol.');
        }

        return $this->apply($pelanggan, SaldoMutationType::Debit, $jumlah, $sumber, $referensi, $keterangan);
    }

    public function hasEnough(Pelanggan $pelanggan, int $jumlah): bool
    {
        return $pelanggan->saldo >= $jumlah;
    }

    private function apply(
        Pelanggan $pelanggan,
        SaldoMutationType $jenis,
        int $jumlah,
        string $sumber,
        ?string $referensi,
        ?string $keterangan,
    ): SaldoMutation {
        return DB::transaction(function () use ($pelanggan, $jenis, $jumlah, $sumber, $referensi, $keterangan): SaldoMutation {
            // Lock baris supaya saldo_akhir yang dihitung tidak balapan.
            /** @var Pelanggan $locked */
            $locked = Pelanggan::query()->lockForUpdate()->findOrFail($pelanggan->getKey());

            $saldoBaru = $jenis === SaldoMutationType::Kredit
                ? $locked->saldo + $jumlah
                : $locked->saldo - $jumlah;

            if ($saldoBaru < 0) {
                throw new LogicException('Saldo tidak mencukupi untuk debit ini.');
            }

            $locked->forceFill(['saldo' => $saldoBaru])->save();

            $mutation = SaldoMutation::query()->create([
                'tenant_id' => $locked->tenant_id,
                'pelanggan_id' => $locked->getKey(),
                'jenis' => $jenis,
                'jumlah' => $jumlah,
                'saldo_akhir' => $saldoBaru,
                'sumber' => $sumber,
                'referensi' => $referensi,
                'keterangan' => $keterangan,
            ]);

            // Sinkronkan instance yang dipegang pemanggil.
            $pelanggan->setAttribute('saldo', $saldoBaru);

            return $mutation;
        });
    }
}
