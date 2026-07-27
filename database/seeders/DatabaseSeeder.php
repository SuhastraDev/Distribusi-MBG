<?php

namespace Database\Seeders;

use App\Models\DistributionRun;
use App\Models\DistributionSchedule;
use App\Models\Location;
use App\Models\Officer;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\User;
use App\Services\GreedyRouteService;
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

        $recipients = [
            ['code' => 'RCV-SCH-0001', 'location_code' => 'SCH-0001', 'name' => 'Siswa SD Negeri 85 Palembang', 'portion_count' => 180],
            ['code' => 'RCV-SCH-0002', 'location_code' => 'SCH-0002', 'name' => 'Siswa SMP Negeri 16 Palembang', 'portion_count' => 220],
            ['code' => 'RCV-SCH-0003', 'location_code' => 'SCH-0003', 'name' => 'Siswa SD Negeri 95 Palembang', 'portion_count' => 160],
            ['code' => 'RCV-SCH-0004', 'location_code' => 'SCH-0004', 'name' => 'Siswa SMP Negeri 30 Palembang', 'portion_count' => 210],
            ['code' => 'RCV-SCH-0005', 'location_code' => 'SCH-0005', 'name' => 'Siswa SD Negeri 70 Palembang', 'portion_count' => 150],
            ['code' => 'RCV-SCH-0006', 'location_code' => 'SCH-0006', 'name' => 'Siswa SMP Negeri 7 Palembang', 'portion_count' => 200],
        ];

        foreach ($recipients as $recipient) {
            $location = Location::query()
                ->where('code', $recipient['location_code'])
                ->firstOrFail();

            Recipient::query()->updateOrCreate(
                ['code' => $recipient['code']],
                [
                    'location_id' => $location->id,
                    'name' => $recipient['name'],
                    'portion_count' => $recipient['portion_count'],
                    'notes' => 'Data dummy untuk demo distribusi MBG.',
                    'status' => 'active',
                ]
            );
        }

        $officer = Officer::query()->where('officer_code', 'PTG-0001')->firstOrFail();
        $depot = Location::query()->where('code', 'DEPOT-SPPG-TT2')->firstOrFail();
        $demoRecipients = Recipient::query()
            ->whereIn('code', ['RCV-SCH-0001', 'RCV-SCH-0002'])
            ->with('location')
            ->get();

        $schedule = DistributionSchedule::query()->updateOrCreate(
            ['code' => 'SCHD-DEMO-001'],
            [
                'scheduled_date' => now()->toDateString(),
                'officer_id' => $officer->id,
                'depot_location_id' => $depot->id,
                'status' => 'scheduled',
                'notes' => 'Jadwal demo awal untuk perencanaan distribusi MBG.',
            ]
        );

        $schedule->destinations()->delete();

        foreach ($demoRecipients->values() as $index => $recipient) {
            $schedule->destinations()->create([
                'location_id' => $recipient->location_id,
                'recipient_id' => $recipient->id,
                'portion_count' => $recipient->portion_count,
                'sequence_order' => $index + 1,
            ]);
        }

        $schedule->recalculateTotalPortions();

        $run = DistributionRun::query()->updateOrCreate(
            ['code' => 'RUN-DEMO-001'],
            [
                'distribution_schedule_id' => $schedule->id,
                'officer_id' => $officer->id,
                'status' => 'ready',
                'notes' => 'Distribusi aktual demo dari jadwal SCHD-DEMO-001.',
            ]
        );

        $run->destinations()->delete();

        foreach ($schedule->destinations()->orderBy('sequence_order')->get() as $destination) {
            $run->destinations()->create([
                'distribution_schedule_destination_id' => $destination->id,
                'location_id' => $destination->location_id,
                'recipient_id' => $destination->recipient_id,
                'planned_portion_count' => $destination->portion_count,
                'sequence_order' => $destination->sequence_order,
                'status' => 'pending',
            ]);
        }

        app(GreedyRouteService::class)->generate($run);
    }
}
