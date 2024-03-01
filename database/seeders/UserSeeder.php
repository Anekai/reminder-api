<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    
    public function run()
    {
        User::create([
            'name'     => 'Usuário Super',
            'email'    => 'super@email.com',
            'password' => bcrypt('super123'),
        ])->assignRole('super');

        User::create([
            'name'     => 'Usuário Suporte',
            'email'    => 'suporte@email.com',
            'password' => bcrypt('suporte123'),
        ])->assignRole('suporte');

        User::create([
            'name'     => 'Usuário Comum',
            'email'    => 'user@email.com',
            'password' => bcrypt('usuario123'),
        ])->assignRole('usuario');
    }
    
}
