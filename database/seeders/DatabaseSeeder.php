<?php

namespace Database\Seeders;

use App\Models\Location;
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

        $locations = [
            [
                'code' => 'DEPOT-SPPG-TT2',
                'name' => 'Depot SPPG Tangga Takat 2',
                'type' => 'depot',
                'address' => 'Tangga Takat, Seberang Ulu II, Palembang',
                'latitude' => -3.0251500,
                'longitude' => 104.7792500,
            ],
            [
                'code' => 'SCH-0001',
                'name' => 'SD Negeri 85 Palembang',
                'type' => 'school',
                'address' => 'Jl. KH Azhari, 7 Ulu, Palembang',
                'latitude' => -2.9959800,
                'longitude' => 104.7647700,
            ],
            [
                'code' => 'SCH-0002',
                'name' => 'SMP Negeri 16 Palembang',
                'type' => 'school',
                'address' => 'Jl. A. Yani, Seberang Ulu II, Palembang',
                'latitude' => -3.0144100,
                'longitude' => 104.7599300,
            ],
            [
                'code' => 'SCH-0003',
                'name' => 'SD Negeri 95 Palembang',
                'type' => 'school',
                'address' => 'Plaju, Palembang',
                'latitude' => -3.0066800,
                'longitude' => 104.7942600,
            ],
            [
                'code' => 'SCH-0004',
                'name' => 'SMP Negeri 30 Palembang',
                'type' => 'school',
                'address' => 'Kertapati, Palembang',
                'latitude' => -3.0358100,
                'longitude' => 104.7451900,
            ],
            [
                'code' => 'SCH-0005',
                'name' => 'SD Negeri 70 Palembang',
                'type' => 'school',
                'address' => 'Jakabaring, Palembang',
                'latitude' => -3.0216200,
                'longitude' => 104.7890400,
            ],
            [
                'code' => 'SCH-0006',
                'name' => 'SMP Negeri 7 Palembang',
                'type' => 'school',
                'address' => 'Seberang Ulu I, Palembang',
                'latitude' => -2.9849300,
                'longitude' => 104.7656300,
            ],
        ];

        foreach ($locations as $location) {
            Location::query()->updateOrCreate(
                ['code' => $location['code']],
                array_merge($location, ['status' => 'active'])
            );
        }
    }
}
