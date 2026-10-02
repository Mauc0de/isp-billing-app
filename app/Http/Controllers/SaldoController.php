<?php

namespace App\Http\Controllers;

use App\Billing\AutoRenew;
use App\Billing\SaldoLedger;
use App\Models\Pelanggan;
use App\Models\SaldoMutation;
use App\Models\Tagihan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LogicException;

class SaldoController extends Controller
{
    public function index(Request $request): View
    {
        $pelanggans = Pelanggan::query()
            ->with('paket')
            ->orderByDesc('saldo')
            ->get();

        $mutations = SaldoMutation::query()
            ->with('pelanggan')
            ->latest()
            ->paginate(30);

        $ringkasan = [
            'total_pelanggan' => $pelanggans->count(),
            'total_saldo' => (int) $pelanggans->sum('saldo'),
            'auto_renew' => $pelanggans->where('auto_renew', true)->count(),
        ];

        return view('saldo.index', compact('pelanggans', 'mutations', 'ringkasan'));
    }

    public function show(string $pelanggan): View
    {
        $pelanggan = Pelanggan::query()->with('paket')->findOrFail($pelanggan);

        $mutations = SaldoMutation::query()
            ->where('pelanggan_id', $pelanggan->getKey())
            ->latest()
            ->paginate(30);

        $tagihans = Tagihan::query()
            ->where('pelanggan_id', $pelanggan->getKey())
            ->orderByDesc('jatuh_tempo')
            ->get();

        return view('saldo.show', compact('pelanggan', 'mutations', 'tagihans'));
    }

    public function topUp(Request $request, string $pelanggan, SaldoLedger $ledger): RedirectResponse
    {
        $pelanggan = Pelanggan::query()->findOrFail($pelanggan);

        $validated = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $ledger->credit(
            pelanggan: $pelanggan,
            jumlah: $validated['jumlah'],
            sumber: 'manual',
            referensi: null,
            keterangan: $validated['keterangan'] ?? 'Top-up manual oleh admin.',
        );

        return back()->with('status', sprintf(
            'Saldo %s ditambah Rp %s.',
            $pelanggan->nama,
            number_format($validated['jumlah'], 0, ',', '.'),
        ));
    }

    public function toggleAutoRenew(string $pelanggan): RedirectResponse
    {
        $pelanggan = Pelanggan::query()->findOrFail($pelanggan);

        $pelanggan->forceFill(['auto_renew' => ! $pelanggan->auto_renew])->save();

        return back()->with('status', sprintf(
            'Auto-renew %s %s.',
            $pelanggan->nama,
            $pelanggan->auto_renew ? 'diaktifkan' : 'dinonaktifkan',
        ));
    }

    public function payInvoice(string $tagihan, AutoRenew $autoRenew): RedirectResponse
    {
        $tagihan = Tagihan::query()->with('pelanggan')->findOrFail($tagihan);

        try {
            $paid = $autoRenew->pay($tagihan);
        } catch (LogicException $exception) {
            return back()->withErrors(['saldo' => $exception->getMessage()]);
        }

        if (! $paid) {
            return back()->withErrors([
                'saldo' => 'Saldo tidak mencukupi atau tagihan ini sudah tidak berstatus outstanding.',
            ]);
        }

        return back()->with('status', "Tagihan {$tagihan->nomor_tagihan} berhasil dilunasi dari saldo.");
    }
}
