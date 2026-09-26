<?php

namespace Database\Seeders;

use App\Auth\PermissionCatalog;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::definitions() as $definition) {
            Permission::query()->updateOrCreate(
                ['slug' => $definition['slug']],
                $definition,
            );
        }
    }
}
