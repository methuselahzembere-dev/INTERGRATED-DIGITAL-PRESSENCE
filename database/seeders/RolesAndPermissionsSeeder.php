<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles and their permissions (Shield format "Action:Model").
 *
 * national_admin gets no explicit permissions: Shield's super-admin gate
 * (config/filament-shield.php) lets it through every check.
 *
 * Permissions only say WHAT a role may do. WHICH affiliate's rows it may do it
 * to is enforced by the visibleTo() scope on the resources' queries.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public const MATRIX = [
        'national_admin' => [],
        'national_user' => [
            'ViewAny:User', 'View:User',
            'ViewAny:Event', 'View:Event',
            'ViewAny:Affiliate', 'View:Affiliate',
        ],
        'affiliate_admin' => [
            'ViewAny:User', 'View:User',
            'ViewAny:Event', 'View:Event', 'Create:Event', 'Update:Event', 'Delete:Event', 'DeleteAny:Event',
        ],
        'affiliate_user' => [
            'ViewAny:Event', 'View:Event',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create the Shield permissions for every resource in the panel. Policies are
        // already generated and committed (app/Policies), so only permissions here;
        // run `shield:generate --all --panel=admin` by hand when you add a resource.
        Artisan::call('shield:generate', ['--all' => true, '--panel' => 'admin', '--option' => 'permissions', '--no-interaction' => true]);

        foreach (self::MATRIX as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions(Permission::whereIn('name', $permissions)->get());
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
