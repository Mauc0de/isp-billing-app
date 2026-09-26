<?php

namespace App\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use LogicException;

class TenantRoleProvisioner
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    /**
     * @return Collection<int, Role>
     */
    public function provision(Tenant $tenant): Collection
    {
        return $this->tenantContext->run($tenant->getKey(), function () use ($tenant): Collection {
            $permissions = Permission::query()
                ->whereIn('slug', array_column(PermissionCatalog::definitions(), 'slug'))
                ->get()
                ->keyBy('slug');

            $catalogSize = count(PermissionCatalog::definitions());

            if ($permissions->count() !== $catalogSize) {
                throw new LogicException('Permission catalog must be registered before provisioning tenant roles.');
            }

            return collect($this->roleDefinitions())
                ->map(function (array $definition) use ($tenant, $permissions): Role {
                    $role = Role::query()->updateOrCreate(
                        [
                            'tenant_id' => $tenant->getKey(),
                            'slug' => $definition['slug'],
                        ],
                        [
                            'name' => $definition['name'],
                            'description' => $definition['description'],
                        ],
                    );

                    $slugs = $definition['permissions'] === '*'
                        ? $permissions->keys()->all()
                        : $definition['permissions'];

                    // Eloquent\Collection::only() memfilter berdasarkan primary key
                    // model, bukan key hasil keyBy('slug'). Karena itu collection
                    // dibungkus Support\Collection agar only() memakai key slug.
                    $permissionRecords = (new Collection($permissions->all()))
                        ->only($slugs)
                        ->mapWithKeys(fn (Permission $permission): array => [
                            $permission->getKey() => ['tenant_id' => $tenant->getKey()],
                        ]);

                    $role->permissions()->sync($permissionRecords->all());

                    return $role;
                });
        });
    }

    /**
     * @return array<string, array{name: string, slug: string, description: string, permissions: string|array<int, string>}>
     */
    private function roleDefinitions(): array
    {
        return [
            'admin' => [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Akses penuh seluruh modul tenant.',
                'permissions' => '*',
            ],
            'finance' => [
                'name' => 'Finance',
                'slug' => 'finance',
                'description' => 'Mengelola tagihan, pembayaran, dan laporan.',
                'permissions' => [
                    PermissionCatalog::DASHBOARD_VIEW,
                    PermissionCatalog::CUSTOMERS_VIEW,
                    PermissionCatalog::PACKAGES_VIEW,
                    PermissionCatalog::INVOICES_VIEW,
                    PermissionCatalog::INVOICES_CREATE,
                    PermissionCatalog::INVOICES_UPDATE,
                    PermissionCatalog::INVOICES_CANCEL,
                    PermissionCatalog::PAYMENTS_VIEW,
                    PermissionCatalog::PAYMENTS_CREATE,
                    PermissionCatalog::PAYMENTS_REVERSE,
                    PermissionCatalog::REPORTS_VIEW,
                    PermissionCatalog::REPORTS_EXPORT,
                ],
            ],
            'staff' => [
                'name' => 'Staff',
                'slug' => 'staff',
                'description' => 'Mengelola pelanggan dan operasional billing.',
                'permissions' => [
                    PermissionCatalog::DASHBOARD_VIEW,
                    PermissionCatalog::CUSTOMERS_VIEW,
                    PermissionCatalog::CUSTOMERS_CREATE,
                    PermissionCatalog::CUSTOMERS_UPDATE,
                    PermissionCatalog::PACKAGES_VIEW,
                    PermissionCatalog::INVOICES_VIEW,
                    PermissionCatalog::PAYMENTS_VIEW,
                    PermissionCatalog::PAYMENTS_CREATE,
                ],
            ],
            'technician' => [
                'name' => 'Technician',
                'slug' => 'technician',
                'description' => 'Memantau router dan tindakan pelanggan.',
                'permissions' => [
                    PermissionCatalog::DASHBOARD_VIEW,
                    PermissionCatalog::CUSTOMERS_VIEW,
                    PermissionCatalog::CUSTOMERS_UPDATE,
                    PermissionCatalog::CUSTOMERS_SUSPEND,
                    PermissionCatalog::CUSTOMERS_REACTIVATE,
                    PermissionCatalog::ROUTERS_VIEW,
                    PermissionCatalog::ROUTERS_MANAGE,
                ],
            ],
        ];
    }
}
