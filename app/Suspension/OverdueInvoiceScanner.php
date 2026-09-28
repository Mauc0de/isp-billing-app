<?php

namespace App\Suspension;

use App\Enums\CustomerStatus;
use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Settings\TenantSettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Mencari tagihan yang layak disuspend dan tagihan yang perlu-ingatkan.
 *
 * Kelas ini HANYA mencari kandidat, tidak pernah mengubah apa pun. Semua
 * perubahan status ditangani CustomerSuspender, supaya aturan suspend hanya
 * punya satu tempat implementasi.
 */
class OverdueInvoiceScanner
{
    public function __construct(private readonly TenantSettings $settings) {}

    /**
     * ID pelanggan yang punya tagihan lewat jatuh tempo lebih lama dari masa
     * tenggang, satu ID per pelanggan meskipun tagihannya banyak.
     *
     * @return list<string>
     */
    public function findSuspendableCustomerIds(): array
    {
        $threshold = Carbon::today()->subDays($this->settings->gracePeriodDays());

        return Invoice::query()
            ->select('customer_id')
            ->whereIn('status', $this->outstandingStatuses())
            ->whereDate('due_date', '<=', $threshold)
            ->whereHas('customer', fn (Builder $query): Builder => $query
                ->where('status', CustomerStatus::Active->value),
            )
            ->distinct()
            ->orderBy('customer_id')
            ->pluck('customer_id')
            ->all();
    }

    /**
     * ID tagihan yang jatuh tempo dalam beberapa hari ke depan dan belum
     * pernah dikirimi pengingat hari ini.
     *
     * @return list<string>
     */
    public function findDueReminderInvoiceIds(): array
    {
        $days = $this->settings->reminderDaysBefore();
        $today = Carbon::today();

        return Invoice::query()
            ->select('id')
            ->whereIn('status', [InvoiceStatus::Unpaid->value, InvoiceStatus::Partial->value])
            ->whereDate('due_date', '>=', $today)
            ->whereDate('due_date', '<=', $today->copy()->addDays($days))
            ->whereHas('customer', fn (Builder $query): Builder => $query
                ->where('status', CustomerStatus::Active->value),
            )
            ->whereDoesntHave('whatsappNotifications', fn (Builder $query): Builder => $query
                ->whereDate('created_at', $today),
            )
            ->orderBy('due_date')
            ->pluck('id')
            ->all();
    }

    /**
     * @return list<string>
     */
    private function outstandingStatuses(): array
    {
        return array_values(array_map(
            static fn (InvoiceStatus $status): string => $status->value,
            array_filter(
                InvoiceStatus::cases(),
                static fn (InvoiceStatus $status): bool => $status->isOutstanding(),
            ),
        ));
    }
}
