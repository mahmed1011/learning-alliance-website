<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Spatie cache clear
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Guard (change if you use a custom one like 'admin')
        $guard = config('auth.defaults.guard', 'web');

        // --- Modules: base first, then children (singular action names) ---
        $modules = [
            [
                'base'     => 'categories',
                'children' => ['category add', 'category edit', 'category delete'],
            ],
            [
                'base'     => 'products',
                'children' => ['product add', 'product edit', 'product delete'],
            ],
            [
                'base'     => 'orders',
                'children' => ['order delete'],
            ],
            [
                'base'     => 'instructions',
                'children' => ['instruction add', 'instruction edit', 'instruction delete'],
            ],
            [
                'base'     => 'contactmessages',
                'children' => ['contactmessage delete'],
            ],
            [
                'base'     => 'user management',
                'children' => ['user add', 'user edit', 'user delete'],
            ],
            [
                'base'     => 'role management',
                'children' => ['role add', 'role edit', 'role delete'],
            ],
            [
                'base'     => 'permission management',
                'children' => ['permission add', 'permission edit', 'permission delete'],
            ],
        ];

        // 1) Create permissions in desired order (base -> children)
        foreach ($modules as $m) {
            // base
            Permission::firstOrCreate([
                'name'       => $m['base'],
                'guard_name' => $guard,
            ]);

            // children
            foreach ($m['children'] as $child) {
                Permission::firstOrCreate([
                    'name'       => $child,
                    'guard_name' => $guard,
                ]);
            }
        }

        // 2) Create Admin role (same guard) & assign ALL permissions
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => $guard]);
        $admin->syncPermissions(Permission::where('guard_name', $guard)->get());

        // Cache refresh
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
