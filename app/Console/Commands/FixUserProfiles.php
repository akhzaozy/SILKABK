<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use App\Models\ParentGuardian;
use App\Models\Therapist;
use App\Models\User;
use Illuminate\Console\Command;

class FixUserProfiles extends Command
{
    protected $signature = 'users:fix-profiles';
    protected $description = 'Create missing profiles for users based on their role';

    public function handle()
    {
        $this->info('Checking users without profiles...');

        $fixed = 0;

        // Fix Orang Tua users without ParentGuardian profile
        $orangTuaUsers = User::where('role', 'orang_tua')
            ->whereDoesntHave('parentGuardian')
            ->get();

        foreach ($orangTuaUsers as $user) {
            ParentGuardian::create([
                'user_id'      => $user->id,
                'name'         => $user->name,
                'relationship' => 'Wali',
                'phone'        => '',
                'address'      => '',
            ]);
            $this->line("✓ Created ParentGuardian profile for: {$user->email}");
            $fixed++;
        }

        // Fix Terapis users without Therapist profile
        $therapisUsers = User::where('role', 'terapis')
            ->whereDoesntHave('therapist')
            ->get();

        foreach ($therapisUsers as $user) {
            Therapist::create([
                'user_id'       => $user->id,
                'name'          => $user->name,
                'phone'         => '',
                'specialization' => 'Umum',
                'is_active'     => true,
            ]);
            $this->line("✓ Created Therapist profile for: {$user->email}");
            $fixed++;
        }

        // Fix Dokter users without Doctor profile
        $dokterUsers = User::where('role', 'dokter')
            ->whereDoesntHave('doctor')
            ->get();

        foreach ($dokterUsers as $user) {
            Doctor::create([
                'user_id'        => $user->id,
                'name'           => $user->name,
                'phone'          => '',
                'specialization' => 'Umum',
                'license_number' => '',
            ]);
            $this->line("✓ Created Doctor profile for: {$user->email}");
            $fixed++;
        }

        if ($fixed === 0) {
            $this->info('No missing profiles found. All users are good!');
        } else {
            $this->success("Fixed {$fixed} user profile(s)!");
        }

        return Command::SUCCESS;
    }
}
