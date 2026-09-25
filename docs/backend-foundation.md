# Fondasi Backend Andi

## Keputusan Arsitektur

- Framework: Laravel 12 dengan PHP minimal 8.2.
- Database produksi: MySQL; test memakai SQLite in-memory.
- Multi-tenancy: shared database dengan `tenant_id`.
- Login: email unik global dan user hanya memiliki satu tenant.
- Primary key domain: ULID.
- Role dan permission: RBAC tenant-scoped tanpa package permission eksternal.
- Status pelanggan harus menyimpan sumber suspensi agar auto-reaktivasi modul automasi tidak melewati suspensi manual.

## Kontrak Tenancy

`TenantContext` adalah sumber tenant aktif untuk seluruh request, command, dan job.

Aturan:

1. Query model tenant-owned di luar context menghasilkan nol hasil, bukan seluruh data.
2. Model tenant-owned tidak dapat dibuat tanpa `tenant_id`.
3. `tenant_id` yang diberikan harus sama dengan tenant context.
4. Middleware `tenant` mengambil tenant dari user yang sedang login.
5. User atau tenant nonaktif tidak boleh mengakses route terproteksi.
6. Context dibersihkan setelah request selesai untuk aman pada worker jangka panjang.

## Tabel Inti

### `tenants`

- Identitas ISP, slug, kontak, timezone, dan status aktif.

### `users`

- Milik satu tenant.
- Email unik global.
- Mendukung status aktif dan waktu login terakhir.

### `packages`

- Harga, siklus billing, bandwidth, dan status aktif.

### `customers`

- Nomor pelanggan per tenant.
- Paket aktif.
- Status `active`, `suspended`, atau `terminated`.
- Sumber suspensi `manual` atau `overdue`.

### `invoices`

- Nomor invoice per tenant.
- Snapshot nama paket dan nominal tagihan.
- Periode, jatuh tempo, status, diskon, dan total.
- Index overdue menggunakan `tenant_id`, `status`, dan `due_date`.

### `payments`

- Relasi invoice dan customer.
- Nominal, metode, status, referensi provider, waktu bayar, dan metadata.

## Urutan Implementasi

1. Tenant context, middleware, migration, dan model inti.
2. RBAC tenant dan authorization middleware.
3. Login, logout, dan pembatasan user nonaktif.
4. Nomor dokumen transaksional yang aman terhadap race condition.
5. Generate invoice dan pencatatan pembayaran dalam database transaction.
6. Event domain untuk integrasi Modul Automasi.
7. Factory, seeder, dan test skenario bisnis.
