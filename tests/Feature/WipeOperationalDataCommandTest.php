<?php

namespace Tests\Feature;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\DistributionSchedule;
use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Officer;
use App\Models\OfficerPosition;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\RoutePlan;
use App\Models\RoutePlanStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WipeOperationalDataCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_all_operational_data_but_keeps_users_and_roles(): void
    {
        $role = Role::query()->firstOrCreate(['name' => 'petugas'], ['display_name' => 'Petugas']);
        $officerUser = User::factory()->create(['role_id' => $role->id]);
        $officer = Officer::factory()->create(['user_id' => $officerUser->id]);

        $depot = Location::factory()->create(['type' => 'depot']);
        $destinationLocation = Location::factory()->create(['type' => 'school']);
        $recipient = Recipient::factory()->create(['location_id' => $destinationLocation->id]);

        $schedule = DistributionSchedule::factory()->create([
            'officer_id' => $officer->id,
            'depot_location_id' => $depot->id,
        ]);
        $scheduleDestination = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $destinationLocation->id,
            'recipient_id' => $recipient->id,
        ]);
        $run = DistributionRun::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $officer->id,
        ]);
        $runDestination = DistributionRunDestination::factory()->create([
            'distribution_run_id' => $run->id,
            'distribution_schedule_destination_id' => $scheduleDestination->id,
            'location_id' => $destinationLocation->id,
            'recipient_id' => $recipient->id,
        ]);
        $routePlan = RoutePlan::factory()->create(['distribution_run_id' => $run->id]);
        RoutePlanStep::factory()->create([
            'route_plan_id' => $routePlan->id,
            'distribution_run_destination_id' => $runDestination->id,
            'location_id' => $destinationLocation->id,
        ]);
        OfficerPosition::factory()->create([
            'distribution_run_id' => $run->id,
            'officer_id' => $officer->id,
        ]);

        $this->artisan('app:wipe-operational-data', ['--force' => true])
            ->assertSuccessful();

        $this->assertSame(0, Location::query()->count());
        $this->assertSame(0, Officer::query()->count());
        $this->assertSame(0, Recipient::query()->count());
        $this->assertSame(0, DistributionSchedule::query()->count());
        $this->assertSame(0, DistributionScheduleDestination::query()->count());
        $this->assertSame(0, DistributionRun::query()->count());
        $this->assertSame(0, DistributionRunDestination::query()->count());
        $this->assertSame(0, RoutePlan::query()->count());
        $this->assertSame(0, RoutePlanStep::query()->count());
        $this->assertSame(0, OfficerPosition::query()->count());

        $this->assertTrue(User::query()->whereKey($officerUser->id)->exists());
        $this->assertTrue(Role::query()->whereKey($role->id)->exists());
    }
}
