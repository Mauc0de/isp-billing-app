# Catatan Perbaikan Merge ke `feature/backend-integrasi-automasi`

Dokumen ini mencatat anomali yang ditemukan dan koreksi yang dilakukan selama
dua proses merge ke branch `feature/backend-integrasi-automasi`:

1. **Merge `main` (`fdac95c`)** — lihat bagian "Merge `main`" di bawah.
2. **Merge `feature/frontend-pelanggan` (`58adda2`)** — lihat bagian
   "Merge `feature/frontend-pelanggan`".

Semua perubahan berada di branch `feature/backend-integrasi-automasi`.

## Konteks

`main` berisi prototipe awal (skeleton Laravel + scaffolding placeholder),
sedangkan `feature/backend-integrasi-automasi` berisi arsitektur seriousness yang
dijelaskan di `docs/backend-foundation.md` (multi-tenancy `tenant_id`,
RBAC tenant-scoped, primary key ULID).

Merge tidak pernah diselesaikan: `.git/MERGE_HEAD` masih ada dan
`app/Models/Tenant.php` masih menyimpan penanda konflik. Akibatnya 10 dari 12
test gagal dan `app/Models/Tenant.php` tidak bisa diparse sebagai PHP.

---

# Merge `main`

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

Tampilan lain **tidak diubah**. Karakter yang tampak rusak di beberapa
view hanyalah artefak decoding terminal, bukan byte rusak di disk. Byte
asli di disk valid UTF-8 (contoh `F0 9F 93 8A` = U+1F4CA), sehingga file
tidak disentuh.

## Format

`laravel/pint` dijalankan. Tiga file diperbaiki, seluruhnya kosmetik:
`app/Auth/PermissionCatalog.php` (baris kosong antar konstanta),
`app/Models/Role.php` dan `app/Models/User.php` (FQN inline diganti import).

---

# Merge `feature/frontend-pelanggan`

Branch `origin/feature/frontend-pelanggan` (commit `58adda2`, 4 commit) berisi
pekerjaan frontend teammates: halaman portal pelanggan, halaman daftar
pelanggan berbasis Livewire, dan redesign dashboard. Branch ini belum pernah
masuk `main`, jadi masih perlu digabung sendiri.

Merge basisnya aman: `main` sudah ikut masuk lewat merge sebelumnya, dan
merge base-nya (`cb0811d`) sudah memuat seluruh commit `main` yang ada saat
itu. Yang perlu ditangani hanya 1 konflik (`routes/web.php`) dan 1 file yang
memang sudah rusak sejak di branch teammates (`layouts/app.blade.php`).

## Ringkasan Anomali

| No | Anomali | Dampak | Koreksi |
|---|---|---|---|
| 9 | `resources/views/layouts/app.blade.php` di `58adda2` **berisi penanda konflik `<<<<<<< HEAD` / `>>>>>>> origin/main` yang sudah ter-commit** | Git tidak menandainya sebagai konflik karena marker dianggap konten biasa, sehingga hasil merge memuat marker yang akan dirender sebagai teks di setiap halaman yang memakai layout | Versi `HEAD` (sidebar SATAK + form logout `POST`) dipulihkan, lalu ditambah `@livewireStyles` / `@livewireScripts` |
| 10 | `resources/views/dashboard/index.blade.php` terbungkus markdown fence, yaitu ```` ```php ```` di baris 1 dan ```` ``` ```` di baris 454 | Teks ```` ```php ```` dan ```` ``` ```` tampil sebagai karakter di atas dan bawah halaman dashboard | Kedua fence dihapus |
| 11 | `routes/web.php` di `58adda2` mengembalikan autentikasi palsu: `POST /login` ke `redirect('/dashboard')` tanpa cek password, `GET /logout` ke `redirect('/')`, tanpa middleware | Membatalkan seluruh perbaikan autentikasi pada merge `main` | Route palsu dibuang; hanya route baru yang dipertahankan |
| 12 | `Route::get('/customers', Index::class)` tanpa layout | Livewire 4 memakai `config('livewire.component_layout')` yang default-nya `layouts::app` dalam mode **component** (butuh `$slot`), sedangkan `layouts/app.blade.php` memakai `@yield('content')` dalam mode **extends**, sehingga berakhir dengan `MissingLayoutException` | `render()` komponen diubah menjadi `view(...)->extends('layouts.app')` |
| 13 | `livewire/livewire: ^4.4` baru ada di `composer.json`, paket belum terpasang | `App\Livewire\Customers\Index` gagal autoload | `composer install` — Livewire v4.4.6 terpasang |

## Penemuan Teknis: Layout Livewire

Livewire 4 **tidak** menyediakan macro `Route::layout()` seperti pada Livewire 3,
sehingga `->layout('layouts.app')` pada route akan melempar
`BadMethodCallException: Method Illuminate\Routing\Route::layout does not exist`.

Layout ditentukan oleh `PageComponentConfig`, yang punya dua mode:

- `type = 'component'` merender dengan `@component($layout->view)` dan `@slot`.
  Dipakai saat view layout merupakan Blade component yang memakai `$slot`.
- `type = 'extends'` merender dengan `@extends($layout->view)` dan
  `@section('content')`. Dipakai saat view layout merupakan template warisan
  klasik.

`resources/views/layouts/app.blade.php` adalah template `@yield('content')`,
jadi mode yang benar adalah `extends`. Macro `View::extends()` bawaan Livewire 4
yang menyetel mode tersebut:

```php
// app/Livewire/Customers/Index.php
public function render()
{
    return view('livewire.customers.index')->extends('layouts.app');
}
```

`config/livewire.php` sengaja **tidak** dibuat. Config bawaan Livewire menunjuk
ke `layouts::app` sebagai component, dan menimpanya dengan config parsial tidak
menyelesaikan masalah mode-nya.

## Perubahan `routes/web.php`

Route dari merge `main` (yang sudah tervalidasi) dipertahankan seluruhnya. Yang
ditambahkan dari branch teammates:

| Route | Perlakuan |
|---|---|
| `GET /portal` | Ditambahkan sebagai route publik, karena isinya halaman landing dan bukan data pelanggan |
| `GET /customers` (Livewire) | Ditambahkan di dalam group `['auth', 'tenant']` dengan `permission:customers.view` |

Route yang **dibuang** dari branch teammates karena bertabrakan dengan
perbaikan autentikasi:

```
GET  /login     -> redirect('/dashboard')   (palsu, tanpa cek password)
POST /login     -> redirect('/dashboard')   (palsu, tanpa cek password)
GET  /register  -> view('auth.register')    (duplikat, tanpa middleware guest)
POST /register  -> redirect('/dashboard')   (palsu, tanpa membuat user)
GET  /logout    -> redirect('/')            (state change lewat GET)
```

Route modul yang juga ada di branch teammates (`/pelanggan`, `/paket`,
`/tagihan`, `/pembayaran`, `/laporan`, `/pengaturan`) **tidak** digandakan:
versi yang dipakai adalah versi bermiddleware permission. Route `customers.index`
memakai `customers.view` yang sama dengan `/pelanggan`.

## Perubahan View

- `resources/views/layouts/app.blade.php` — dipulihkan ke versi `HEAD`, ditambah
  `@livewireStyles` di dalam `<head>` dan `@livewireScripts` sebelum `</body>`.
- `resources/views/dashboard/index.blade.php` — dua markdown fence dihapus,
  isi lainnya tidak diubah.
- `resources/views/portal/index.blade.php` dan
  `resources/views/livewire/customers/index.blade.php` — tidak disentuh.
- `app/Livewire/Customers/Index.php` — hanya `render()` seperti di atas. Data
  dummy bawaan teammates tetap apa adanya.

## Verifikasi Merge `feature/frontend-pelanggan`

- `composer install` — Livewire v4.4.6 terpasang, `package:discover` bersih.
- `php artisan route:list` — 15 route, tidak ada route autentikasi palsu.
- `php artisan test` — **12 passed** (20 assertions).
- `vendor/bin/pint` — 1 style issue diperbaiki pada
  `app/Livewire/Customers/Index.php` (indentasi array).
- `php artisan migrate:fresh --seed` — 5 migration dan seeder berjalan bersih.
- Uji HTTP langsung dengan `php artisan serve`:

  | Skenario | Hasil |
  |---|---|
  | Guest membuka `/`, `/login`, `/register`, `/portal` | 200 |
  | Guest membuka `/dashboard`, `/customers`, `/laporan` | 302 ke `/login` |
  | Login `admin@demo.test` | 302 ke `/dashboard` |
  | Admin membuka 9 halaman modul termasuk `/customers` | semua 200 |
  | `/customers` merender di dalam layout SATAK | sidebar dan form logout `POST` ada |
  | `/customers` menampilkan data dummy | Andi, Budi, Dewi beserta nomor HP |
  | `/customers` menyuntik aset Livewire | styles di `<head>`, script sebelum `</body>` |
  | `/customers` sebagai `staff` | 200, karena `staff` punya `customers.view` |
  | `/laporan` dan `/pengaturan` sebagai `staff` | 403 |
  | `POST /logout` | 302 ke `/login`, lalu `/dashboard` 302 ke `/login` |
  | `GET /logout` | 405 |
  | Penanda konflik dan markdown fence di seluruh view | tidak ada |

---

# Ringkasan Akhir

## Verifikasi Merge `main`

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
- **Redesign dashboard dari teammates tidak diubah.** `dashboard/index.blade.php`
  kini menjadi halaman HTML mandiri (full document) dan tidak lagi memakai
  `layouts.app`. Konsekuensinya halaman tersebut kehilangan sidebar SATAK
  beserta tombol logout. Ini disengaja: tuyaunya adalah pekerjaan desain
  teammates, bukan anomali. Lihat tindak lanjut nomor 6.
- **`/portal` dibiarkan publik** sesuai desain teammates. Halaman tersebut
  tidak membaca data pelanggan dari database.
- `app/Livewire/Customers/Index.php` masih memakai data dummy hardcoded. Tidak
  dikquery ke tabel `customers`.
- `package-lock.json` hanya berubah pada field `name`
  (`satak_keuangan` menjadi `isp-billing-app`) mengikuti rename repository.
  Tidak ada dependensi npm baru.

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
   `route('pelanggan')`. Selain itu `npm install` belum pernah dijalankan di
   mesin lokal, jadi `npm run build` belum diverifikasi.
5. `Session` dan `cache` memakai driver `database`; tabel `sessions` sudah ada
   di `0001_01_01_000000_create_users_table.php`.
6. **Dashboard tidak punya tombol logout** karena menjadi halaman mandiri.
   Pilihannya: dikembalikan ke `@extends('layouts.app')`, atau tombol logout
   ditambahkan di dalam view dashboard itu sendiri.
7. **Dua halaman daftar pelanggan sekarang ada**: `/pelanggan` (view statis)
   dan `/customers` (Livewire, data dummy). Perlu diputuskan mana yang
   dipertahankan sebelum masuk `main`.
8. `config/livewire.php` belum dipublish. Default Livewire masih
   dipakai, termasuk `component_layout` yang tidak relevan karena layout
   ditetapkan di `render()`.
