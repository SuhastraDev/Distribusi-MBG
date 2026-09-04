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
use Carbon\CarbonInterface;
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
        $roles = $this->seedRoles();
        $this->seedUsersAndOfficers($roles);
        $this->seedLocations();
        $this->seedRecipients();
        $this->seedDemoSchedulesAndRuns();
    }

    /**
     * @return array<string, Role>
     */
    private function seedRoles(): array
    {
        return [
            'admin' => Role::query()->updateOrCreate(
                ['name' => 'admin'],
                ['display_name' => 'Admin']
            ),
            'petugas' => Role::query()->updateOrCreate(
                ['name' => 'petugas'],
                ['display_name' => 'Petugas Distribusi']
            ),
            'kepala_sppg' => Role::query()->updateOrCreate(
                ['name' => 'kepala_sppg'],
                ['display_name' => 'Kepala SPPG']
            ),
        ];
    }

    /**
     * @param  array<string, Role>  $roles
     */
    private function seedUsersAndOfficers(array $roles): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@distribusimbg.test'],
            [
                'role_id' => $roles['admin']->id,
                'name' => 'Admin Distribusi MBG',
                'phone' => '081200000001',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'kepala@distribusimbg.test'],
            [
                'role_id' => $roles['kepala_sppg']->id,
                'name' => 'Kepala SPPG Tangga Takat 2',
                'phone' => '081200000002',
                'status' => 'active',
                'password' => Hash::make('password'),
            ]
        );

        $officers = [
            [
                'email' => 'petugas@distribusimbg.test',
                'user_name' => 'Petugas Distribusi 1',
                'officer_code' => 'PTG-0001',
                'officer_name' => 'Rizky Pratama',
                'phone' => '081200000003',
            ],
            [
                'email' => 'petugas2@distribusimbg.test',
                'user_name' => 'Petugas Distribusi 2',
                'officer_code' => 'PTG-0002',
                'officer_name' => 'Dewi Lestari',
                'phone' => '081200000004',
            ],
        ];

        foreach ($officers as $officerData) {
            $user = User::query()->updateOrCreate(
                ['email' => $officerData['email']],
                [
                    'role_id' => $roles['petugas']->id,
                    'name' => $officerData['user_name'],
                    'phone' => $officerData['phone'],
                    'status' => 'active',
                    'password' => Hash::make('password'),
                ]
            );

            Officer::query()->updateOrCreate(
                ['officer_code' => $officerData['officer_code']],
                [
                    'user_id' => $user->id,
                    'name' => $officerData['officer_name'],
                    'phone' => $officerData['phone'],
                    'address' => 'SPPG Tangga Takat 2 Palembang',
                    'status' => 'active',
                ]
            );
        }
    }

    private function seedLocations(): void
    {
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
            [
                'code' => 'PKM-0001',
                'name' => 'Puskesmas Boom Baru',
                'type' => 'puskesmas',
                'address' => 'Boom Baru, Palembang',
                'latitude' => -2.9754512,
                'longitude' => 104.7824651,
            ],
            [
                'code' => 'PKM-0002',
                'name' => 'Puskesmas Pembantu 16 Ulu Talang Banten',
                'type' => 'puskesmas',
                'address' => '16 Ulu, Seberang Ulu II, Palembang',
                'latitude' => -2.9989607,
                'longitude' => 104.7845731,
            ],
        ];

        foreach ($locations as $location) {
            Location::query()->updateOrCreate(
                ['code' => $location['code']],
                array_merge($location, ['status' => 'active'])
            );
        }
    }

    private function seedRecipients(): void
    {
        $recipients = [
            ['code' => 'RCV-SCH-0001', 'location_code' => 'SCH-0001', 'name' => 'Siswa SD Negeri 85 Palembang', 'portion_count' => 180],
            ['code' => 'RCV-SCH-0002', 'location_code' => 'SCH-0002', 'name' => 'Siswa SMP Negeri 16 Palembang', 'portion_count' => 220],
            ['code' => 'RCV-SCH-0003', 'location_code' => 'SCH-0003', 'name' => 'Siswa SD Negeri 95 Palembang', 'portion_count' => 160],
            ['code' => 'RCV-SCH-0004', 'location_code' => 'SCH-0004', 'name' => 'Siswa SMP Negeri 30 Palembang', 'portion_count' => 210],
            ['code' => 'RCV-SCH-0005', 'location_code' => 'SCH-0005', 'name' => 'Siswa SD Negeri 70 Palembang', 'portion_count' => 150],
            ['code' => 'RCV-SCH-0006', 'location_code' => 'SCH-0006', 'name' => 'Siswa SMP Negeri 7 Palembang', 'portion_count' => 200],
            ['code' => 'RCV-PKM-0001', 'location_code' => 'PKM-0001', 'name' => 'Pasien Puskesmas Boom Baru', 'portion_count' => 90],
            ['code' => 'RCV-PKM-0002', 'location_code' => 'PKM-0002', 'name' => 'Pasien Puskesmas Pembantu 16 Ulu Talang Banten', 'portion_count' => 70],
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
    }

    private function seedDemoSchedulesAndRuns(): void
    {
        $depot = Location::query()->where('code', 'DEPOT-SPPG-TT2')->firstOrFail();
        $officerOne = Officer::query()->where('officer_code', 'PTG-0001')->firstOrFail();
        $officerTwo = Officer::query()->where('officer_code', 'PTG-0002')->firstOrFail();

        $this->seedDemoRun(
            scheduleCode: 'SCHD-DEMO-AKTIF',
            runCode: 'RUN-DEMO-AKTIF',
            officer: $officerOne,
            depot: $depot,
            recipientCodes: ['RCV-SCH-0001', 'RCV-SCH-0002', 'RCV-SCH-0003'],
            scheduledDate: now(),
            runStatus: 'in_progress',
            scheduleNotes: 'Jadwal demo distribusi aktif untuk simulasi monitoring realtime.',
            runNotes: 'Distribusi aktif demo: satu tujuan terkirim, satu tiba, satu menunggu.',
        );

        $this->seedDemoRun(
            scheduleCode: 'SCHD-DEMO-SELESAI',
            runCode: 'RUN-DEMO-SELESAI',
            officer: $officerTwo,
            depot: $depot,
            recipientCodes: ['RCV-SCH-0004', 'RCV-SCH-0005', 'RCV-SCH-0006'],
            scheduledDate: now()->subDay(),
            runStatus: 'completed',
            scheduleNotes: 'Jadwal demo distribusi selesai untuk laporan skripsi.',
            runNotes: 'Distribusi selesai demo: seluruh tujuan sudah terkirim.',
        );
    }

    /**
     * @param  array<int, string>  $recipientCodes
     */
    private function seedDemoRun(
        string $scheduleCode,
        string $runCode,
        Officer $officer,
        Location $depot,
        array $recipientCodes,
        CarbonInterface $scheduledDate,
        string $runStatus,
        string $scheduleNotes,
        string $runNotes,
    ): void {
        $recipients = Recipient::query()
            ->whereIn('code', $recipientCodes)
            ->with('location')
            ->get()
            ->sortBy(fn (Recipient $recipient): int => array_search($recipient->code, $recipientCodes, true))
            ->values();

        $schedule = DistributionSchedule::query()->updateOrCreate(
            ['code' => $scheduleCode],
            [
                'scheduled_date' => $scheduledDate->toDateString(),
                'officer_id' => $officer->id,
                'depot_location_id' => $depot->id,
                'status' => $runStatus === 'completed' ? 'completed' : 'scheduled',
                'notes' => $scheduleNotes,
            ]
        );

        $run = DistributionRun::query()->updateOrCreate(
            ['code' => $runCode],
            [
                'distribution_schedule_id' => $schedule->id,
                'officer_id' => $officer->id,
                'status' => $runStatus,
                'started_at' => $this->startedAtFor($runStatus, $scheduledDate),
                'completed_at' => $runStatus === 'completed' ? $scheduledDate->copy()->setTime(9, 25) : null,
                'notes' => $runNotes,
            ]
        );

        $run->routePlan?->delete();
        $run->officerPositions()->delete();
        $run->destinations()->delete();
        $schedule->destinations()->delete();

        foreach ($recipients as $index => $recipient) {
            $scheduleDestination = $schedule->destinations()->create([
                'location_id' => $recipient->location_id,
                'recipient_id' => $recipient->id,
                'portion_count' => $recipient->portion_count,
                'sequence_order' => $index + 1,
            ]);

            $run->destinations()->create($this->destinationPayload(
                runStatus: $runStatus,
                scheduleDestinationId: $scheduleDestination->id,
                recipient: $recipient,
                sequenceOrder: $index + 1,
                scheduledDate: $scheduledDate,
            ));
        }

        $schedule->recalculateTotalPortions();

        $run->load('destinations.location', 'schedule.depot');
        app(GreedyRouteService::class)->generate($run);
        $this->seedOfficerPositions($run);
    }

    /**
     * @return array<string, mixed>
     */
    private function destinationPayload(
        string $runStatus,
        int $scheduleDestinationId,
        Recipient $recipient,
        int $sequenceOrder,
        CarbonInterface $scheduledDate,
    ): array {
        $isCompletedRun = $runStatus === 'completed';
        $isDeliveredInActiveRun = $runStatus === 'in_progress' && $sequenceOrder === 1;
        $isArrivedInActiveRun = $runStatus === 'in_progress' && $sequenceOrder === 2;

        return [
            'distribution_schedule_destination_id' => $scheduleDestinationId,
            'location_id' => $recipient->location_id,
            'recipient_id' => $recipient->id,
            'planned_portion_count' => $recipient->portion_count,
            'delivered_portion_count' => $isCompletedRun || $isDeliveredInActiveRun ? $recipient->portion_count : null,
            'sequence_order' => $sequenceOrder,
            'status' => match (true) {
                $isCompletedRun, $isDeliveredInActiveRun => 'delivered',
                $isArrivedInActiveRun => 'arrived',
                default => 'pending',
            },
            'arrived_at' => $isCompletedRun || $isDeliveredInActiveRun || $isArrivedInActiveRun
                ? $scheduledDate->copy()->setTime(8, 20 + ($sequenceOrder * 10))
                : null,
            'delivered_at' => $isCompletedRun || $isDeliveredInActiveRun
                ? $scheduledDate->copy()->setTime(8, 30 + ($sequenceOrder * 10))
                : null,
            'proof_notes' => $isCompletedRun || $isDeliveredInActiveRun
                ? 'Porsi diterima sesuai data demo skripsi.'
                : ($isArrivedInActiveRun ? 'Petugas sudah tiba dan sedang serah terima.' : null),
        ];
    }

    private function startedAtFor(string $runStatus, CarbonInterface $scheduledDate): ?CarbonInterface
    {
        if ($runStatus === 'ready') {
            return null;
        }

        if ($runStatus === 'completed') {
            return $scheduledDate->copy()->setTime(8, 0);
        }

        return now()->subMinutes(45);
    }

    private function seedOfficerPositions(DistributionRun $run): void
    {
        $run->loadMissing('officer', 'schedule.depot');

        $positions = $run->status === 'completed'
            ? [
                ['latitude' => -3.0216200, 'longitude' => 104.7890400, 'recorded_at' => $run->completed_at ?? now()->subDay()],
            ]
            : [
                ['latitude' => -3.0251500, 'longitude' => 104.7792500, 'recorded_at' => now()->subMinutes(40)],
                ['latitude' => -3.0144100, 'longitude' => 104.7599300, 'recorded_at' => now()->subMinutes(10)],
            ];

        collect($positions)->each(function (array $position) use ($run): void {
            $run->officerPositions()->create([
                'officer_id' => $run->officer_id,
                'latitude' => $position['latitude'],
                'longitude' => $position['longitude'],
                'accuracy_meters' => 12.5,
                'recorded_at' => $position['recorded_at'],
            ]);
        });
    }
}
