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
use App\Models\User;
use App\Services\GreedyRouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficerPositionMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_officer_can_update_position_for_own_in_progress_distribution_run(): void
    {
        $officerUser = $this->createUserWithRole('petugas');
        $officer = Officer::factory()->create(['user_id' => $officerUser->id]);
        $run = $this->createRun($officer, ['status' => 'in_progress']);

        $this->actingAs($officerUser)
            ->post(route('distribution-runs.positions.store', $run), [
                'latitude' => -3.0251500,
                'longitude' => 104.7792500,
                'accuracy_meters' => 12.5,
                'recorded_at' => '2026-08-01 10:00:00',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('officer_positions', [
            'distribution_run_id' => $run->id,
            'officer_id' => $officer->id,
            'latitude' => -3.0251500,
            'longitude' => 104.7792500,
            'accuracy_meters' => 12.5,
        ]);
    }

    public function test_position_update_requires_in_progress_distribution_run(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun($this->createOfficer(), ['status' => 'ready']);

        $this->actingAs($admin)
            ->post(route('distribution-runs.positions.store', $run), [
                'latitude' => -3.0251500,
                'longitude' => 104.7792500,
            ])
            ->assertStatus(422);
    }

    public function test_officer_cannot_update_other_officer_position(): void
    {
        $officerUser = $this->createUserWithRole('petugas');
        Officer::factory()->create(['user_id' => $officerUser->id]);
        $otherRun = $this->createRun($this->createOfficer(), ['status' => 'in_progress']);

        $this->actingAs($officerUser)
            ->post(route('distribution-runs.positions.store', $otherRun), [
                'latitude' => -3.0251500,
                'longitude' => 104.7792500,
            ])
            ->assertForbidden();
    }

    public function test_position_validation_requires_valid_coordinates(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun($this->createOfficer(), ['status' => 'in_progress']);

        $this->actingAs($admin)
            ->post(route('distribution-runs.positions.store', $run), [
                'latitude' => -91,
                'longitude' => 181,
                'accuracy_meters' => -1,
            ])
            ->assertSessionHasErrors(['latitude', 'longitude', 'accuracy_meters']);
    }

    public function test_latest_position_endpoint_returns_newest_position(): void
    {
        $head = $this->createUserWithRole('kepala_sppg');
        $officer = $this->createOfficer();
        $run = $this->createRun($officer, ['status' => 'in_progress']);

        OfficerPosition::factory()->create([
            'distribution_run_id' => $run->id,
            'officer_id' => $officer->id,
            'latitude' => -3.1,
            'longitude' => 104.7,
            'recorded_at' => '2026-08-01 09:00:00',
        ]);
        OfficerPosition::factory()->create([
            'distribution_run_id' => $run->id,
            'officer_id' => $officer->id,
            'latitude' => -3.2,
            'longitude' => 104.8,
            'accuracy_meters' => 9.75,
            'recorded_at' => '2026-08-01 10:00:00',
        ]);

        $this->actingAs($head)
            ->get(route('distribution-runs.positions.latest', $run))
            ->assertOk()
            ->assertJsonPath('distribution.code', $run->code)
            ->assertJsonPath('officer.name', $officer->name)
            ->assertJsonPath('position.latitude', -3.2)
            ->assertJsonPath('position.longitude', 104.8)
            ->assertJsonPath('position.accuracy_meters', 9.75);
    }

    public function test_route_plan_map_data_includes_latest_officer_position(): void
    {
        $admin = $this->createUserWithRole('admin');
        $officer = $this->createOfficer();
        $run = $this->createRun($officer, ['status' => 'in_progress']);
        $routePlan = app(GreedyRouteService::class)->generate($run);

        OfficerPosition::factory()->create([
            'distribution_run_id' => $run->id,
            'officer_id' => $officer->id,
            'latitude' => -3.0251500,
            'longitude' => 104.7792500,
            'accuracy_meters' => 11.25,
            'recorded_at' => '2026-08-01 10:00:00',
        ]);

        $this->actingAs($admin)
            ->get(route('route-plans.map-data', $routePlan))
            ->assertOk()
            ->assertJsonPath('officer_position.latitude', -3.02515)
            ->assertJsonPath('officer_position.longitude', 104.77925)
            ->assertJsonPath('officer_position.accuracy_meters', 11.25);
    }

    public function test_route_plan_detail_renders_officer_position_marker_script(): void
    {
        $admin = $this->createUserWithRole('admin');
        $officer = $this->createOfficer();
        $run = $this->createRun($officer, ['status' => 'in_progress']);
        $routePlan = app(GreedyRouteService::class)->generate($run);
        OfficerPosition::factory()->create([
            'distribution_run_id' => $run->id,
            'officer_id' => $officer->id,
            'latitude' => -3.0251500,
            'longitude' => 104.7792500,
            'recorded_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('route-plans.show', $routePlan))
            ->assertOk()
            ->assertSee('officerPosition')
            ->assertSee('Posisi Petugas');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRun(Officer $officer, array $attributes = []): DistributionRun
    {
        $depot = Location::factory()->create(['type' => 'depot']);
        $location = Location::factory()->create();
        $recipient = Recipient::factory()->create(['location_id' => $location->id]);
        $schedule = DistributionSchedule::factory()->create([
            'officer_id' => $officer->id,
            'depot_location_id' => $depot->id,
            'status' => 'scheduled',
        ]);
        $scheduleDestination = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $location->id,
            'recipient_id' => $recipient->id,
            'portion_count' => $recipient->portion_count,
        ]);
        $run = DistributionRun::factory()->create(array_merge([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $officer->id,
        ], $attributes));

        DistributionRunDestination::factory()->create([
            'distribution_run_id' => $run->id,
            'distribution_schedule_destination_id' => $scheduleDestination->id,
            'location_id' => $location->id,
            'recipient_id' => $recipient->id,
            'planned_portion_count' => $recipient->portion_count,
        ]);

        return $run;
    }

    private function createOfficer(): Officer
    {
        return Officer::factory()->create([
            'user_id' => $this->createUserWithRole('petugas')->id,
            'status' => 'active',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUserWithRole(string $roleName, array $attributes = []): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            ['display_name' => str($roleName)->replace('_', ' ')->title()->toString()]
        );

        return User::factory()->create(array_merge([
            'role_id' => $role->id,
            'status' => 'active',
        ], $attributes));
    }
}
