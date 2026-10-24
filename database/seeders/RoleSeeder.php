<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin'],
            ['name' => 'Admin',       'slug' => 'admin'],
            ['name' => 'Orang Tua',   'slug' => 'orang_tua'],
            ['name' => 'Terapis',     'slug' => 'terapis'],
            ['name' => 'Dokter',      'slug' => 'dokter'],
            ['name' => 'Psikolog',    'slug' => 'psikolog'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}