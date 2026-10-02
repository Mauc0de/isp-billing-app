<?php

namespace App\Livewire\Portal;

use App\Enums\PaymentRequestPurpose;
use App\Models\PaymentRequest;
use App\Models\Tagihan;
use App\Payments\PaymentGatewayFactory;
use App\Settings\TenantSettings;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts::portal')]
class PembayaranSaya extends Component
{
    use WithFileUploads, WithPagination;

    public $pelanggan;

    public ?string $tagihanId = null;

    public int $jumlah = 0;

    public string $tujuan = 'topup_saldo';

    public $bukti;

    public function mount(): void
    {
        $this->pelanggan = auth()->user()?->pelanggan ?? null;
    }

    public function ajukan(PaymentGatewayFactory $factory): void
    {
        $this->validate([
            'tujuan' => ['required', 'in:topup_saldo,bayar_tagihan'],
            'jumlah' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'tagihanId' => ['nullable', 'string'],
            'bukti' => ['required', 'image', 'max:'.config('payment.bukti.max_kb', 4096)],
        ], [
            'bukti.required' => 'Unggah bukti transfer terlebih dahulu.',
            'bukti.image' => 'Bukti transfer harus berupa gambar.',
        ]);

        if ($this->pelanggan === null) {
            $this->addError('jumlah', 'Akun ini tidak tertaut ke data pelanggan.');

            return;
        }

        $tagihan = null;

        if ($this->tujuan === PaymentRequestPurpose::BayarTagihan->value && $this->tagihanId !== null) {
            $tagihan = Tagihan::query()
                ->where('pelanggan_id', $this->pelanggan->getKey())
                ->find($this->tagihanId);

            if ($tagihan === null) {
                $this->addError('tagihanId', 'Tagihan tidak ditemukan.');

                return;
            }
        }

        $gateway = $factory->make();

        $request = $gateway->createRequest(
            pelanggan: $this->pelanggan,
            jumlah: $this->jumlah,
            tujuan: $this->tujuan,
            tagihan: $tagihan,
        );

        $path = $this->bukti->store('bukti-transfer', 'local');
        $request->forceFill(['bukti_path' => $path])->save();

        $this->reset(['bukti', 'jumlah', 'tagihanId']);
        $this->tujuan = PaymentRequestPurpose::TopUpSaldo->value;

        session()->flash('portal_status', 'Pembayaran terkirim. Mohon tunggu verifikasi admin.');
    }

    public function render(TenantSettings $settings)
    {
        $tagihans = $this->pelanggan
            ? Tagihan::query()
                ->where('pelanggan_id', $this->pelanggan->getKey())
                ->whereIn('status', ['belum_bayar', 'sebagian', 'terlambat'])
                ->orderBy('jatuh_tempo')
                ->get()
            : collect();

        $riwayat = $this->pelanggan
            ? PaymentRequest::query()
                ->where('pelanggan_id', $this->pelanggan->getKey())
                ->latest()
                ->paginate(10)
            : PaymentRequest::query()->whereRaw('1 = 0')->paginate(10);

        return view('livewire.portal.pembayaran-saya', [
            'tagihans' => $tagihans,
            'riwayat' => $riwayat,
            'bank' => $settings->bank(),
        ]);
    }
}
