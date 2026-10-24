<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\ParentGuardian;
use App\Models\Psychologist;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@uptdblkabk.id'],
            [
                'role'      => 'super_admin',
                'name'      => 'Super Admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Admin
        User::updateOrCreate(
            ['email' => 'admin@uptdblkabk.id'],
            [
                'role'      => 'admin',
                'name'      => 'Admin',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Orang Tua
        $orangTua = User::updateOrCreate(
            ['email' => 'orangtua@uptdblkabk.id'],
            [
                'role'      => 'orang_tua',
                'name'      => 'Budi Santoso',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Buat profil ParentGuardian untuk user orang_tua
        ParentGuardian::updateOrCreate(
            ['user_id' => $orangTua->id],
            [
                'name'         => 'Budi Santoso',
                'relationship' => 'Ayah',
                'phone'        => '081234567890',
                'address'      => 'Jl. Contoh No. 123, Jakarta',
            ]
        );

        // Terapis
        $terapis = User::updateOrCreate(
            ['email' => 'terapis@uptdblkabk.id'],
            [
                'role'      => 'terapis',
                'name'      => 'Siti Nurhaliza',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Buat profil Therapist
        Therapist::updateOrCreate(
            ['user_id' => $terapis->id],
            [
                'name'           => 'Siti Nurhaliza',
                'phone'          => '082345678901',
                'specialization' => 'Terapi Wicara',
                'is_active'      => true,
            ]
        );

        // Dokter
        $dokter = User::updateOrCreate(
            ['email' => 'dokter@uptdblkabk.id'],
            [
                'role'      => 'dokter',
                'name'      => 'Dr. Ahmad Wijaya',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Buat profil Doctor
        Doctor::updateOrCreate(
            ['user_id' => $dokter->id],
            [
                'name'           => 'Dr. Ahmad Wijaya',
                'phone'          => '083456789012',
                'specialization' => 'Anak',
                'license_number' => 'SIP-001',
            ]
        );

        // Psikolog
        $psikolog = User::updateOrCreate(
            ['email' => 'psikolog@uptdblkabk.id'],
            [
                'role'      => 'psikolog',
                'name'      => 'Psi. Dewi Rahayu',
                'password'  => Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Buat profil Psychologist
        Psychologist::updateOrCreate(
            ['user_id' => $psikolog->id],
            [
                'nama'         => 'Dewi Rahayu',
                'spesialisasi' => 'Psikologi Anak',
                'nomor_sip'    => 'SIP-PSI-001',
                'telepon'      => '084567890123',
            ]
        );
    }
}