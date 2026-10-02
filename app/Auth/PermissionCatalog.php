<?php

namespace App\Auth;

final class PermissionCatalog
{
    public const DASHBOARD_VIEW = 'dashboard.view';

    public const CUSTOMERS_VIEW = 'customers.view';
    public const CUSTOMERS_CREATE = 'customers.create';
    public const CUSTOMERS_UPDATE = 'customers.update';
    public const CUSTOMERS_ARCHIVE = 'customers.archive';
    public const CUSTOMERS_SUSPEND = 'customers.suspend';
    public const CUSTOMERS_REACTIVATE = 'customers.reactivate';

    public const PACKAGES_VIEW = 'packages.view';
    public const PACKAGES_CREATE = 'packages.create';
    public const PACKAGES_UPDATE = 'packages.update';
    public const PACKAGES_ARCHIVE = 'packages.archive';

    public const INVOICES_VIEW = 'invoices.view';
    public const INVOICES_CREATE = 'invoices.create';
    public const INVOICES_UPDATE = 'invoices.update';
    public const INVOICES_CANCEL = 'invoices.cancel';

    public const PAYMENTS_VIEW = 'payments.view';
    public const PAYMENTS_CREATE = 'payments.create';
    public const PAYMENTS_REVERSE = 'payments.reverse';

    public const REPORTS_VIEW = 'reports.view';
    public const REPORTS_EXPORT = 'reports.export';

    public const ROUTERS_VIEW = 'routers.view';
    public const ROUTERS_MANAGE = 'routers.manage';

    public const USERS_VIEW = 'users.view';
    public const USERS_CREATE = 'users.create';
    public const USERS_UPDATE = 'users.update';
    public const USERS_DELETE = 'users.delete';

    public const ROLES_VIEW = 'roles.view';
    public const ROLES_MANAGE = 'roles.manage';

    public const SETTINGS_VIEW = 'settings.view';
    public const SETTINGS_UPDATE = 'settings.update';

    public const AUDIT_VIEW = 'audit.view';

    /**
     * @return list<array{name: string, slug: string, permission_group: string, description: string}>
     */
    public static function definitions(): array
    {
        return [
            ['name' => 'Lihat dashboard', 'slug' => self::DASHBOARD_VIEW, 'permission_group' => 'dashboard', 'description' => 'Melihat ringkasan dashboard.'],

            ['name' => 'Lihat pelanggan', 'slug' => self::CUSTOMERS_VIEW, 'permission_group' => 'customers', 'description' => 'Melihat daftar pelanggan.'],            ['name' => 'Buat pelanggan', 'slug' => self::CUSTOMERS_CREATE, 'permission_group' => 'customers', 'description' => 'Menambah pelanggan.'],
            ['name' => 'Ubah pelanggan', 'slug' => self::CUSTOMERS_UPDATE, 'permission_group' => 'customers', 'description' => 'Mengubah data pelanggan.'],
            ['name' => 'Arsipkan pelanggan', 'slug' => self::CUSTOMERS_ARCHIVE, 'permission_group' => 'customers', 'description' => 'Mengarsipkan pelanggan.'],
            ['name' => 'Suspend pelanggan', 'slug' => self::CUSTOMERS_SUSPEND, 'permission_group' => 'customers', 'description' => 'Menyuspend pelanggan.'],
            ['name' => 'Reaktivasi pelanggan', 'slug' => self::CUSTOMERS_REACTIVATE, 'permission_group' => 'customers', 'description' => 'Mengaktifkan kembali pelanggan.'],

            ['name' => 'Lihat paket', 'slug' => self::PACKAGES_VIEW, 'permission_group' => 'packages', 'description' => 'Melihat paket langganan.'],
            ['name' => 'Buat paket', 'slug' => self::PACKAGES_CREATE, 'permission_group' => 'packages', 'description' => 'Menambah paket.'],
            ['name' => 'Ubah paket', 'slug' => self::PACKAGES_UPDATE, 'permission_group' => 'packages', 'description' => 'Mengubah paket.'],
            ['name' => 'Arsipkan paket', 'slug' => self::PACKAGES_ARCHIVE, 'permission_group' => 'packages', 'description' => 'Mengarsipkan paket.'],

            ['name' => 'Lihat tagihan', 'slug' => self::INVOICES_VIEW, 'permission_group' => 'invoices', 'description' => 'Melihat tagihan.'],
            ['name' => 'Buat tagihan', 'slug' => self::INVOICES_CREATE, 'permission_group' => 'invoices', 'description' => 'Membuat tagihan.'],
            ['name' => 'Ubah tagihan', 'slug' => self::INVOICES_UPDATE, 'permission_group' => 'invoices', 'description' => 'Mengubah tagihan sebelum lunas.'],
            ['name' => 'Batalkan tagihan', 'slug' => self::INVOICES_CANCEL, 'permission_group' => 'invoices', 'description' => 'Membatalkan tagihan.'],

            ['name' => 'Lihat pembayaran', 'slug' => self::PAYMENTS_VIEW, 'permission_group' => 'payments', 'description' => 'Melihat riwayat pembayaran.'],
            ['name' => 'Catat pembayaran', 'slug' => self::PAYMENTS_CREATE, 'permission_group' => 'payments', 'description' => 'Mencatat pembayaran.'],
            ['name' => 'Batalkan pembayaran', 'slug' => self::PAYMENTS_REVERSE, 'permission_group' => 'payments', 'description' => 'Membalik pembayaran.'],

            ['name' => 'Lihat laporan', 'slug' => self::REPORTS_VIEW, 'permission_group' => 'reports', 'description' => 'Melihat laporan.'],
            ['name' => 'Export laporan', 'slug' => self::REPORTS_EXPORT, 'permission_group' => 'reports', 'description' => 'Mengekspor laporan.'],

            ['name' => 'Lihat router', 'slug' => self::ROUTERS_VIEW, 'permission_group' => 'routers', 'description' => 'Melihat router dan status koneksi.'],
            ['name' => 'Kelola router', 'slug' => self::ROUTERS_MANAGE, 'permission_group' => 'routers', 'description' => 'Mengelola konfigurasi router.'],

            ['name' => 'Lihat pengguna', 'slug' => self::USERS_VIEW, 'permission_group' => 'users', 'description' => 'Melihat pengguna.'],
            ['name' => 'Buat pengguna', 'slug' => self::USERS_CREATE, 'permission_group' => 'users', 'description' => 'Menambah pengguna.'],
            ['name' => 'Ubah pengguna', 'slug' => self::USERS_UPDATE, 'permission_group' => 'users', 'description' => 'Mengubah pengguna.'],
            ['name' => 'Hapus pengguna', 'slug' => self::USERS_DELETE, 'permission_group' => 'users', 'description' => 'Menghapus pengguna.'],

            ['name' => 'Lihat role', 'slug' => self::ROLES_VIEW, 'permission_group' => 'roles', 'description' => 'Melihat role.'],
            ['name' => 'Kelola role', 'slug' => self::ROLES_MANAGE, 'permission_group' => 'roles', 'description' => 'Mengelola role dan permission.'],

            ['name' => 'Lihat pengaturan', 'slug' => self::SETTINGS_VIEW, 'permission_group' => 'settings', 'description' => 'Melihat pengaturan tenant.'],
            ['name' => 'Ubah pengaturan', 'slug' => self::SETTINGS_UPDATE, 'permission_group' => 'settings', 'description' => 'Mengubah pengaturan tenant.'],

            ['name' => 'Lihat audit log', 'slug' => self::AUDIT_VIEW, 'permission_group' => 'audit', 'description' => 'Melihat audit log.'],
        ];
    }
}
