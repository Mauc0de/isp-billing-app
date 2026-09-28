<?php

namespace App\Whatsapp;

use App\Contracts\WhatsappGateway;
use App\Enums\WhatsappProvider;
use App\Settings\TenantSettings;

/**
 * Memilih implementasi gateway sesuai provider yang dipakai tenant.
 *
 * Provider dipilih per tenant lewat tabel settings, sedangkan token API dibaca
 * dari .env lewat config(). Dengan begitu satu token dari kantor bisa dipakai
 * semua tenant tanpa konfigurasi ulang.
 */
class WhatsappGatewayFactory
{
    public function __construct(private readonly TenantSettings $settings) {}

    public function make(): WhatsappGateway
    {
        return match ($this->settings->whatsappProvider()) {
            WhatsappProvider::Fonnte => $this->fonnte(),
            WhatsappProvider::Wablas => $this->wablas(),
            WhatsappProvider::Disabled => new NullGateway,
        };
    }

    private function fonnte(): WhatsappGateway
    {
        $token = config('whatsapp.fonnte.token');

        if (! is_string($token) || $token === '') {
            return new NullGateway;
        }

        return new FonnteGateway(
            token: $token,
            baseUrl: (string) config('whatsapp.fonnte.base_url'),
            countryCode: (string) config('whatsapp.fonnte.country_code'),
            connectOnly: (bool) config('whatsapp.fonnte.connect_only'),
        );
    }

    private function wablas(): WhatsappGateway
    {
        $token = config('whatsapp.wablas.token');
        $secretKey = config('whatsapp.wablas.secret_key');

        // Authorization Wablas adalah "token.secret". Kalau secret kosong, header
        // terkirim dalam bentuk tidak lengkap dan provider menolaknya dengan
        // pesan yang membingungkan, jadi lebih baik tidak dikirim sama sekali.
        if (! is_string($token) || $token === '' || ! is_string($secretKey) || $secretKey === '') {
            return new NullGateway;
        }

        return new WablasGateway(
            token: $token,
            secretKey: $secretKey,
            baseUrl: (string) config('whatsapp.wablas.base_url'),
            sendRefId: (bool) config('whatsapp.wablas.send_ref_id'),
        );
    }
}
