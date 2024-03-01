<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'super',
            'guard_name' => 'sanctum'
        ]);

        $supportRole = Role::create([
            'name' => 'suporte',
            'guard_name' => 'sanctum'
        ]);

        $userRole = Role::create([
            'name' => 'usuario',
            'guard_name' => 'sanctum'
        ]);

        Permission::create(['name' => 'cadastro-solicitacoes', 'guard_name' => 'sanctum'])->syncRoles([$userRole]);
        Permission::create(['name' => 'cadastro-usuarios', 'guard_name' => 'sanctum'])->syncRoles([$supportRole]);
        Permission::create(['name' => 'painel-monitoramento', 'guard_name' => 'sanctum'])->syncRoles([$supportRole]);
    }
}
