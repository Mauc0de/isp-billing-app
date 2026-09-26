# Catatan Perbaikan Merge `main` → `feature/backend-integrasi-automasi`

Dokumen ini mencatat anomali yang ditemukan saat proses merge branch `main`
(commit `fdac95c`) ke branch `feature/backend-integrasi-automasi`, serta koreksi
yang dilakukan. Semua perubahan berada di branch `feature/backend-integrasi-automasi`.

## Konteks

`main` berisi prototipe awal (skeleton Laravel + scaffolding placeholder),
sedangkan `feature/backend-integrasi-automasi` berisi arsitektur seriousness yang
dijelaskan di `docs/backend-foundation.md` (multi-tenancy `tenant_id`,
RBAC tenant-scoped, primary key ULID).

Merge tidak pernah diselesaikan: `.git/MERGE_HEAD` masih ada dan
`app/Models/Tenant.php` masih menyimpan penanda konflik. Akibatnya 10 dari 12
test gagal dan `app/Models/Tenant.php` tidak bisa diparse sebagai PHP.

## Ringkasan Anomali

| No | Anomali | Dampak | Korreksi |
|---|---|---|---|
| 1 | `app/Models/Tenant.php` berisi penanda konflik `<<<<<<< HEAD` yang belum diselesaikan | Parse error, seluruh fondasi tidak bisa dimuat | Konflik diselesaikan ke sisi `HEAD` (versi lengkap) |
| 2 | `2026_09_25_071951_create_tenants_table.php` membuat tabel `tenants` kedua | `table "tenants" already exists`; kolom bentrok (`nama`/`kode`/`aktif` vs `name`/`slug`/`is_active`); `id()` bigint bentrok dengan seluruh `foreignUlid` | File dihapus |
| 3 | `2026_09_25_073910_add_tenant_id_to_users_table.php` menambah `users.tenant_id` | Duplikat kolom; `foreignId` nullable bentrok dengan `foreignUlid` NOT NULL + unique komposit `users_id_tenant_unique` | File dihapus |
| 4 | `create_pelanggans` / `create_pakets` / `create_tagihans` / `create_pembayarans_table.php` | 4 tabel placeholder (`id` + `timestamps` saja) yang tidak dipakai model mana pun | File dihapus |
| 5 | `app/Models/Paket.php`, `Pelanggan.php`, `Pembayaran.php`, `Tagihan.php` | Stub `Model` kosong yang menduplikasi `Package`/`Customer`/`Payment`/`Invoice`. `Pelanggan.php` mendeklarasikan `class pelanggan` (huruf kecil) sehingga tidak pernah autoload | File dihapus |
| 6 | `routes/web.php` dari `main` memakai `session()->put('user_id', 1)` | Login tanpa cek password, tanpa cek `is_active`, tanpa tenant; seluruh `ResolveTenant` / `EnsureRole` / `EnsurePermission` mati; logout lewat GET | Ditulis ulang (lihat below) |
| 7 | `app/Auth/TenantRoleProvisioner.php` memakai `only()` pada `Eloquent\Collection` | **Bug latent**: setiap role dibuat dengan **0 permission** | Diperbaiki |
| 8 | Tidak ada direktori `lang/` | `__('auth.failed')` dan pesan validasi tampil sebagai teks mentah | `lang/en/auth.php` + `lang/id/auth.php` ditambahkan |

## File yang Dihapus (10)

Sembilan di antaranya berasal dari `main` dan seluruh isinya duplikat atau
placeholder. Riwayat versinya tetap tersedia di git.

```
app/Models/Paket.php
app/Models/Pelanggan.php
app/Models/Pembayaran.php
app/Models/Tagihan.php
database/migrations/2026_09_25_071951_create_tenants_table.php
database/migrations/2026_09_25_072038_create_pelanggans_table.php
database/migrations/2026_09_25_072134_create_pakets_table.php
database/migrations/2026_09_25_072220_create_tagihans_table.php
database/migrations/2026_09_25_072237_create_pembayarans_table.php
database/migrations/2026_09_25_073910_add_tenant_id_to_users_table.php
```

Tabel `tenants` dan kolom `users.tenant_id` **tetap ada** — keduanya memang
sudah dibuat di `0001_01_01_000000_create_users_table.php` dengan skema ULID
yang konsisten dengan dokumen fondasi. Yang dihapus hanya definisi keduanya.

## File yang Ditambahkan (3)

```
app/Http/Controllers/Auth/AuthenticatedSessionController.php
lang/en/auth.php
lang/id/auth.php
```

## Perubahan `routes/web.php`

URL dan seluruh view tidak berubah. Yang berubah adalah cara autentikasi dan
perlindungannya.

- `POST /login` → `AuthenticatedSessionController@store`. Memakai `Auth::validate()`,
  menolak user nonaktif, menolak tenant nonaktif, memperbarui `last_login_at`,
  dan meregenerasi session id.
- `POST /register` → `AuthenticatedSessionController@register`. Minta `tenant_slug`
  karena `users.tenant_id` bersifat `NOT NULL` — pendaftaran tidak membuat tenant
  baru. User baru langsung diberi role `staff`.
- `POST /logout` → `AuthenticatedSessionController@destroy`. Threats state change
  sekarang memakai `POST` + `@csrf`, bukan `GET`.
- Semua route modul memakai `['auth', 'tenant']` ditambah permission
  middleware yang sesuai:

  | Route | Permission |
  |---|---|
  | `/dashboard` | `dashboard.view` |
  | `/pelanggan` | `customers.view` |
  | `/paket` | `packages.view` |
  | `/tagihan` | `invoices.view` |
  | `/pembayaran` | `payments.view` |
  | `/laporan` | `reports.view` |
  | `/pengaturan` | `settings.view` |

- `POST /login` diberi `throttle:6,1`.
- Route diberi nama (`login`, `dashboard`, `logout`, dst).

## Bug Latent: Permission Role Kosong

`TenantRoleProvisioner` memfilter permission dengan:

```php
$permissions->only($slugs)   // $permissions = Eloquent\Collection::keyBy('slug')
```

`Eloquent\Collection::only()` memfilter berdasarkan **primary key model**, bukan
key hasil `keyBy('slug')`. Karena itu hasilnya selalu kosong dan
`sync()` tidak pernah menempelkan apa pun: **admin, finance, staff, dan
technician semuanya tercipta dengan 0 permission**. Gejalanya tidak muncul di
sebelum merge karena semua test sudah gagal lebih dulu pada tahap migration.

Koreksinya adalah membungkus collection dengan `Support\Collection` agar
`only()` memakai key slug:

```php
$permissionRecords = (new Collection($permissions->all()))
    ->only($slugs)
    ->mapWithKeys(...);
```

Hasil setelah koreksi:

| Role | Permission |
|---|---|
| admin | 31 |
| finance | 12 |
| staff | 8 |
| technician | 7 |

## Perubahan View

- `resources/views/auth/register.blade.php` — menambah field `tenant_slug`,
  field `password_confirmation`, `old()` pada input, dan menampilkan seluruh
  pesan error (bukan hanya yang pertama).
- `resources/views/layouts/app.blade.php` — link Logout diganti form `POST`
  dengan `@csrf` dan `route('logout')`.

Tampilan lain **tidak diubah**. Stronger: karakter yang tampak rusak di
beberapa view (`dY"�`, `�+`) hanyalah artefak decoding terminal. Byte asli di
disk valid UTF-8 (contoh `F0 9F 93 8A` = 📊), sehingga file tidak disentuh.

## Format

`laravel/pint` dijalankan. Tiga file diperbaiki, seluruhnya kosmetik:
`app/Auth/PermissionCatalog.php` (baris kosong antar konstanta),
`app/Models/Role.php` dan `app/Models/User.php` (FQN inline diganti import).

## Verifikasi

- `php artisan migrate` — 5 migration berjalan bersih tanpa konflik.
- `php artisan test` — **12 passed** (sebelum perbaikan: 2 passed, 10 failed).
- `vendor/bin/pint --test` — bersih.
- Uji HTTP langsung dengan `php artisan serve`:

  | Skenario | Hasil |
  |---|---|
  | Guest membuka `/dashboard` | 302 ke `/login` |
  | Login password salah | 302 kembali + pesan "These credentials do not match our records." |
  | Login benar | 302 ke `/dashboard` |
  | 7 halaman modul sebagai admin | semua 200 |
  | `/laporan` dan `/pengaturan` sebagai `staff` | 403 |
  | `/login` saat sudah login | 302 ke `/dashboard` |
  | `POST /logout` | 302 ke `/login`, lalu `/dashboard` 302 ke `/login` |
  | `GET /logout` | 405 |
  | Registrasi `tenant_slug` tidak valid | 302 kembali + pesan validasi |
  | Registrasi `tenant_slug=demo-isp` | 302 ke `/dashboard`, lalu 200 |

## Yang Sengaja Tidak Diubah

- Tampilan halaman modul masih data statis dummy. Halaman tersebut masih
  closure di `routes/web.php` tanpa controller dan tanpa query ke database.
  Itu pekerjaan fitur, bukan perbaikan anomali.
- Nama tabel dan model tetap memakai bahasa Inggris
  (`customers`, `invoices`, `payments`, `packages`) sesuai dokumen fondasi.
  Stub bahasa Indonesia yang menduikatinya sudah dihapus, bukan diterjemahkan.
- `public/favicon.ico` berukuran 0 byte. Tidak dipakai view mana pun dan di
  luar cakupan.
- `database/database.sqlite` tidak ada di mesin lokal sehingga `optimize:clear`
  gagal. File itu sudah dibuat dan masuk `.gitignore`.

## Tindak Lanjut (Belum Dikerjakan)

1. **Sidebar belum menyembunyikan menu sesuai permission.** User `staff` tetap
   melihat "Laporan" dan "Pengaturan", lalu mendapat 403 saat diklik. Sebaiknya
   memakai `@can('reports.view')` di `resources/views/layouts/app.blade.php`.
2. **Pendaftaran masih terbuka.** Siapa pun yang mengetahui `tenant_slug` bisa
   mendaftar. Pertimbangkan invite token atau persetujuan admin.
3. **Pesan validasi masih bahasa Inggris.** `.env` lokal memakai
   `APP_LOCALE=en` sedangkan `.env.example` memakai `APP_LOCALE=id`, dan
   `lang/id/validation.php` belum ada.
4. **Menu sidebar masih memakai path literal** (`href="/pelanggan"`) alih-alih
   `route('pelanggan')`.
5. `Session` dan `cache` memakai driver `database`; tabel `sessions` sudah ada
   di `0001_01_01_000000_create_users_table.php`.
