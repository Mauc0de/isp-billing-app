<?php

namespace App\Http\Controllers;

use App\Models\PaymentRequest;
use App\Payments\PaymentRequestVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use LogicException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $requests = PaymentRequest::query()
            ->with(['pelanggan', 'tagihan'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $ringkasan = [
            'menunggu' => PaymentRequest::query()->where('status', 'menunggu')->count(),
            'terverifikasi' => PaymentRequest::query()->where('status', 'terverifikasi')->count(),
            'ditolak' => PaymentRequest::query()->where('status', 'ditolak')->count(),
        ];

        return view('payment-requests.index', compact('requests', 'ringkasan', 'status'));
    }

    public function approve(Request $request, string $paymentRequest, PaymentRequestVerifier $verifier): RedirectResponse
    {
        $model = PaymentRequest::query()->findOrFail($paymentRequest);

        $validated = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $verifier->approve($model, $request->user(), $validated['catatan_admin'] ?? null);
        } catch (LogicException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return back()->with('status', "Pembayaran {$model->pelanggan?->nama} sebesar Rp ".number_format($model->jumlah, 0, ',', '.').' telah diverifikasi.');
    }

    public function reject(Request $request, string $paymentRequest, PaymentRequestVerifier $verifier): RedirectResponse
    {
        $model = PaymentRequest::query()->findOrFail($paymentRequest);

        $validated = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $verifier->reject($model, $request->user(), $validated['catatan_admin'] ?? null);
        } catch (LogicException $exception) {
            return back()->withErrors(['payment' => $exception->getMessage()]);
        }

        return back()->with('status', 'Pembayaran ditolak.');
    }

    public function proof(string $paymentRequest): StreamedResponse
    {
        $model = PaymentRequest::query()->findOrFail($paymentRequest);

        abort_if($model->bukti_path === null, 404);

        abort_unless(Storage::disk('local')->exists($model->bukti_path), 404);

        return Storage::disk('local')->response($model->bukti_path);
    }
}
