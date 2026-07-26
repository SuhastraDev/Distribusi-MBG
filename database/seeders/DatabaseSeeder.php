<?php

namespace Database\Seeders;

use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->updateOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Admin']
        );

        $officerRole = Role::query()->updateOrCreate(
            ['name' => 'petugas'],
            ['display_name' => 'Petugas Distribusi']
        );

        $headRole = Role::query()->updateOrCreate(
            ['name' => 'kepala_sppg'],
            ['display_name' => 'Kepala SPPG']
        );

        User::query()->updateOrCreate(
            ['email' => 'admin@distribusimbg.test'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Admin Distribusi MBG',
                'phone' => '081200000001',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'kepala@distribusimbg.test'],
            [
                'role_id' => $headRole->id,
                'name' => 'Kepala SPPG',
                'phone' => '081200000002',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        $officerUser = User::query()->updateOrCreate(
            ['email' => 'petugas@distribusimbg.test'],
            [
                'role_id' => $officerRole->id,
                'name' => 'Petugas Distribusi',
                'phone' => '081200000003',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        Officer::query()->updateOrCreate(
            ['officer_code' => 'PTG-0001'],
            [
                'user_id' => $officerUser->id,
                'name' => 'Petugas Distribusi',
                'phone' => '081200000003',
                'address' => 'SPPG Tangga Takat 2 Palembang',
                'status' => 'active',
            ]
        );
    }
}
