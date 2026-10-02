<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Voucher;
use App\Models\VoucherBatch;
use App\Vouchers\VoucherGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $vouchers = Voucher::query()
            ->with(['paket', 'batch'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(50)
            ->withQueryString();

        $batches = VoucherBatch::query()
            ->with('paket')
            ->withCount('vouchers')
            ->latest()
            ->take(20)
            ->get();

        $pakets = Paket::query()->orderBy('nama_paket')->get();

        $ringkasan = [
            'total' => Voucher::query()->count(),
            'belum_dipakai' => Voucher::query()->where('status', 'belum_dipakai')->count(),
            'terpakai' => Voucher::query()->where('status', 'terpakai')->count(),
        ];

        return view('vouchers.index', compact('vouchers', 'batches', 'pakets', 'ringkasan', 'status'));
    }

    public function store(Request $request, VoucherGenerator $generator): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'paket_id' => ['nullable', 'string', 'exists:pakets,id'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:1000'],
            'masa_aktif_hari' => ['required', 'integer', 'min:1', 'max:365'],
            'kode_prefix' => ['nullable', 'string', 'max:20'],
            'panjang_kode' => ['required', 'integer', 'min:6', 'max:24'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $paket = isset($validated['paket_id'])
            ? Paket::query()->findOrFail($validated['paket_id'])
            : null;

        $batch = $generator->generate(
            nama: $validated['nama'],
            paket: $paket,
            jumlah: $validated['jumlah'],
            masaAktifHari: $validated['masa_aktif_hari'],
            prefix: $validated['kode_prefix'] ?? null,
            panjangKode: $validated['panjang_kode'],
            catatan: $validated['catatan'] ?? null,
        );

        return redirect()
            ->route('vouchers.batch', $batch)
            ->with('status', "Berhasil membuat {$batch->jumlah} voucher.");
    }

    public function batch(string $batch): View
    {
        $batch = VoucherBatch::query()->with(['paket', 'vouchers' => fn ($query) => $query->orderBy('kode')])
            ->findOrFail($batch);

        return view('vouchers.batch', compact('batch'));
    }

    public function destroy(string $voucher): RedirectResponse
    {
        $voucher = Voucher::query()->findOrFail($voucher);

        abort_unless($voucher->status->isUsable(), 403, 'Hanya voucher yang belum dipakai dapat dihapus.');

        $kode = $voucher->kode;
        $voucher->delete();

        return back()->with('status', "Voucher {$kode} dihapus.");
    }
}
