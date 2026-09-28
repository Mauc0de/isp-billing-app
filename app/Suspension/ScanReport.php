<?php

namespace App\Suspension;

/**
 * Ringkasan hasil satu putaran pemindaian tagihan.
 *
 * Dipakai command isp:scan-overdue untuk menampilkan angka yang sebenarnya.
 * Angka ini penting: command yang menampilkan flag alih-alih jumlah hasil
 * akan membuat operator mengira pelanggan ikut disuspend padahal tidak ada
 * yang dikerjakan.
 */
final readonly class ScanReport
{
    public function __construct(
        public int $tenants,
        public int $suspended,
        public int $reminded,
    ) {}

    public static function empty(): self
    {
        return new self(tenants: 0, suspended: 0, reminded: 0);
    }

    /**
     * True kalau tidak ada apa pun yang perlu dikerjakan.
     *
     * Bukan berarti tidak ada masalah — tenant yang belum punya router atau
     * data tagihan sama sekali juga menghasilkan report kosong.
     */
    public function foundNothing(): bool
    {
        return $this->suspended === 0 && $this->reminded === 0;
    }

    public function summary(): string
    {
        return sprintf(
            '%d tenant diperiksa, %d pelanggan diantrekan untuk suspend, %d pengingat jatuh tempo.',
            $this->tenants,
            $this->suspended,
            $this->reminded,
        );
    }
}
