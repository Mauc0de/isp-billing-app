<?php

namespace App\Http\Controllers;

use App\Enums\WhatsappProvider;
use App\Settings\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pengaturan per tenant.
 *
 * Semua nilai ditulis lewat TenantSettings::put() supaya cache internalnya
 * ikut dibersihkan. Token API (WhatsApp/Fonnte/Wablas) sengaja TIDAK ada di
 * sini karena kolom settings berupa JSON plaintext; token tetap dari .env.
 */
class SettingController extends Controller
{
    public function index(TenantSettings $settings): View
    {
        return view('pengaturan.index', [
            'gracePeriodDays' => $settings->gracePeriodDays(),
            'reminderDaysBefore' => $settings->reminderDaysBefore(),
            'whatsappProvider' => $settings->whatsappProvider()->value,
            'providers' => WhatsappProvider::cases(),
            'templates' => [
                'suspend' => $settings->template('suspend'),
                'reactivate' => $settings->template('reactivate'),
                'due_reminder' => $settings->template('due_reminder'),
            ],
            'bank' => $settings->bank(),
            'placeholders' => '{customer} {customer_number} {tenant} {package} {router} {invoice_number} {amount} {due_date} {overdue_days} {reason}',
        ]);
    }

    public function update(Request $request, TenantSettings $settings): RedirectResponse
    {
        $validated = $request->validate([
            'grace_period_days' => ['required', 'integer', 'min:0', 'max:90'],
            'reminder_days_before' => ['required', 'integer', 'min:0', 'max:30'],
            'whatsapp_provider' => ['required', 'string', 'in:disabled,fonnte,wablas'],
            'template_suspend' => ['required', 'string', 'max:1000'],
            'template_reactivate' => ['required', 'string', 'max:1000'],
            'template_due_reminder' => ['required', 'string', 'max:1000'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_number' => ['required', 'string', 'max:50'],
            'bank_holder' => ['required', 'string', 'max:100'],
        ]);

        $settings->put(TenantSettings::GRACE_PERIOD_DAYS, (int) $validated['grace_period_days'], 'integer');
        $settings->put(TenantSettings::REMINDER_DAYS_BEFORE, (int) $validated['reminder_days_before'], 'integer');
        $settings->put(TenantSettings::WHATSAPP_PROVIDER, $validated['whatsapp_provider']);
        $settings->put(TenantSettings::WHATSAPP_TEMPLATE_SUSPEND, $validated['template_suspend']);
        $settings->put(TenantSettings::WHATSAPP_TEMPLATE_REACTIVATE, $validated['template_reactivate']);
        $settings->put(TenantSettings::WHATSAPP_TEMPLATE_DUE_REMINDER, $validated['template_due_reminder']);
        $settings->put(TenantSettings::BANK_NAME, $validated['bank_name']);
        $settings->put(TenantSettings::BANK_NUMBER, $validated['bank_number']);
        $settings->put(TenantSettings::BANK_HOLDER, $validated['bank_holder']);

        return back()->with('status', 'Pengaturan berhasil disimpan.');
    }
}
