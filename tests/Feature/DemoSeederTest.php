<?php

namespace Tests\Feature;

use App\Models\DistributionRun;
use App\Models\DistributionSchedule;
use App\Models\Location;
use App\Models\Officer;
use App\Models\OfficerPosition;
use App\Models\Recipient;
use App\Models\RoutePlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_complete_demo_data_for_thesis_presentation(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'admin@distribusimbg.test', 'status' => 'active']);
        $this->assertDatabaseHas('users', ['email' => 'kepala@distribusimbg.test', 'status' => 'active']);
        $this->assertDatabaseHas('users', ['email' => 'petugas@distribusimbg.test', 'status' => 'active']);
        $this->assertDatabaseHas('users', ['email' => 'petugas2@distribusimbg.test', 'status' => 'active']);

        $this->assertSame(4, User::query()->count());
        $this->assertSame(2, Officer::query()->where('status', 'active')->count());
        $this->assertSame(1, Location::query()->where('type', 'depot')->count());
        $this->assertGreaterThanOrEqual(6, Location::query()->where('type', 'school')->count());
        $this->assertSame(6, Recipient::query()->where('status', 'active')->count());

        $this->assertDatabaseHas('distribution_schedules', ['code' => 'SCHD-DEMO-AKTIF']);
        $this->assertDatabaseHas('distribution_schedules', ['code' => 'SCHD-DEMO-SELESAI', 'status' => 'completed']);
        $this->assertSame(2, DistributionSchedule::query()->count());

        $activeRun = DistributionRun::query()
            ->where('code', 'RUN-DEMO-AKTIF')
            ->with(['destinations', 'routePlan.steps', 'latestOfficerPosition'])
            ->firstOrFail();

        $completedRun = DistributionRun::query()
            ->where('code', 'RUN-DEMO-SELESAI')
            ->with(['destinations', 'routePlan.steps'])
            ->firstOrFail();

        $this->assertSame('in_progress', $activeRun->status);
        $this->assertSame('completed', $completedRun->status);
        $this->assertCount(3, $activeRun->destinations);
        $this->assertCount(3, $completedRun->destinations);
        $this->assertSame(1, $activeRun->destinations->where('status', 'delivered')->count());
        $this->assertSame(1, $activeRun->destinations->where('status', 'arrived')->count());
        $this->assertSame(3, $completedRun->destinations->where('status', 'delivered')->count());

        $this->assertSame(2, RoutePlan::query()->count());
        $this->assertNotNull($activeRun->routePlan);
        $this->assertNotNull($completedRun->routePlan);
        $this->assertGreaterThanOrEqual(4, $activeRun->routePlan->steps->count());
        $this->assertGreaterThanOrEqual(4, $completedRun->routePlan->steps->count());
        $this->assertGreaterThan(0, (float) $activeRun->routePlan->total_distance_km);
        $this->assertGreaterThan(0, (float) $completedRun->routePlan->total_distance_km);

        $this->assertGreaterThanOrEqual(3, OfficerPosition::query()->count());
        $this->assertNotNull($activeRun->latestOfficerPosition);
    }

    public function test_database_seeder_can_be_run_twice_without_duplicate_demo_records(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(4, User::query()->count());
        $this->assertSame(2, Officer::query()->count());
        $this->assertSame(7, Location::query()->count());
        $this->assertSame(6, Recipient::query()->count());
        $this->assertSame(2, DistributionSchedule::query()->count());
        $this->assertSame(2, DistributionRun::query()->count());
        $this->assertSame(2, RoutePlan::query()->count());
    }
}
