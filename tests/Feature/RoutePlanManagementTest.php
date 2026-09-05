<?php

namespace Tests\Feature;

use App\Models\DistributionRun;
use App\Models\DistributionRunDestination;
use App\Models\DistributionSchedule;
use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Officer;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\RoutePlan;
use App\Models\User;
use App\Services\GreedyRouteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutePlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_greedy_service_orders_nearest_destination_first(): void
    {
        $run = $this->createRunWithDeterministicCoordinates();

        $routePlan = app(GreedyRouteService::class)->generate($run);
        $steps = $routePlan->steps()->with('location')->get();

        $this->assertSame('Depot Test', $steps[0]->location->name);
        $this->assertSame('Sekolah Terdekat', $steps[1]->location->name);
        $this->assertSame('Sekolah Terjauh', $steps[2]->location->name);
        $this->assertSame('greedy_nearest_neighbor', $routePlan->algorithm);
        $this->assertGreaterThan(0, (float) $routePlan->total_distance_km);
        $this->assertGreaterThan(0, $routePlan->total_estimated_minutes);
    }

    public function test_assigned_officer_can_generate_route_plan_from_distribution_run(): void
    {
        $officer = $this->createOfficer();
        $run = $this->createRunWithDeterministicCoordinates($officer);

        $this->actingAs($officer->user)
            ->post(route('distribution-runs.route-plan.generate', $run))
            ->assertRedirect();

        $this->assertDatabaseHas('route_plans', [
            'distribution_run_id' => $run->id,
            'algorithm' => 'greedy_nearest_neighbor',
            'status' => 'generated',
        ]);

        $routePlan = RoutePlan::query()->where('distribution_run_id', $run->id)->firstOrFail();

        $this->assertDatabaseHas('route_plan_steps', [
            'route_plan_id' => $routePlan->id,
            'step_order' => 1,
            'step_type' => 'start',
        ]);
        $this->assertCount(3, $routePlan->steps);
    }

    public function test_admin_cannot_generate_route_plan(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRunWithDeterministicCoordinates();

        $this->actingAs($admin)
            ->post(route('distribution-runs.route-plan.generate', $run))
            ->assertForbidden();
    }

    public function test_generate_route_plan_is_idempotent_for_same_distribution_run(): void
    {
        $officer = $this->createOfficer();
        $run = $this->createRunWithDeterministicCoordinates($officer);

        $this->actingAs($officer->user)->post(route('distribution-runs.route-plan.generate', $run));
        $this->actingAs($officer->user)->post(route('distribution-runs.route-plan.generate', $run));

        $this->assertSame(1, RoutePlan::query()->where('distribution_run_id', $run->id)->count());
        $this->assertSame(3, RoutePlan::query()->where('distribution_run_id', $run->id)->firstOrFail()->steps()->count());
    }

    public function test_admin_can_view_route_plan_list_and_detail(): void
    {
        $admin = $this->createUserWithRole('admin');
        $routePlan = app(GreedyRouteService::class)->generate($this->createRunWithDeterministicCoordinates());

        $this->actingAs($admin)
            ->get(route('route-plans.index'))
            ->assertOk()
            ->assertSee('Rute Greedy Distribusi')
            ->assertSee($routePlan->code);

        $this->actingAs($admin)
            ->get(route('route-plans.show', $routePlan))
            ->assertOk()
            ->assertSee('Detail Rute Greedy')
            ->assertSee('route-map')
            ->assertSee('tile.openstreetmap.org')
            ->assertSee(route('route-plans.map-data', $routePlan))
            ->assertSee('Sekolah Terdekat')
            ->assertSee('Sekolah Terjauh');
    }

    public function test_route_plan_map_data_returns_coordinates_and_route_metadata(): void
    {
        $admin = $this->createUserWithRole('admin');
        $routePlan = app(GreedyRouteService::class)->generate($this->createRunWithDeterministicCoordinates());

        $this->actingAs($admin)
            ->get(route('route-plans.map-data', $routePlan))
            ->assertOk()
            ->assertJsonPath('route.code', $routePlan->code)
            ->assertJsonPath('route.algorithm', 'greedy_nearest_neighbor')
            ->assertJsonPath('distribution.code', $routePlan->run->code)
            ->assertJsonPath('center.latitude', 0)
            ->assertJsonPath('center.longitude', 0)
            ->assertJsonPath('steps.0.type', 'start')
            ->assertJsonPath('steps.0.location.name', 'Depot Test')
            ->assertJsonPath('steps.1.location.name', 'Sekolah Terdekat')
            ->assertJsonCount(3, 'steps');
    }

    public function test_officer_can_only_generate_route_for_own_distribution_run(): void
    {
        $officerUser = $this->createUserWithRole('petugas');
        $officer = Officer::factory()->create(['user_id' => $officerUser->id]);
        $ownRun = $this->createRunWithDeterministicCoordinates($officer);
        $otherRun = $this->createRunWithDeterministicCoordinates($this->createOfficer());

        $this->actingAs($officerUser)
            ->post(route('distribution-runs.route-plan.generate', $ownRun))
            ->assertRedirect();

        $this->actingAs($officerUser)
            ->post(route('distribution-runs.route-plan.generate', $otherRun))
            ->assertForbidden();
    }

    public function test_officer_only_sees_own_route_plans_in_index(): void
    {
        $ownOfficer = $this->createOfficer();
        $ownRoutePlan = app(GreedyRouteService::class)->generate($this->createRunWithDeterministicCoordinates($ownOfficer));
        $otherRoutePlan = app(GreedyRouteService::class)->generate($this->createRunWithDeterministicCoordinates($this->createOfficer()));

        $this->actingAs($ownOfficer->user)
            ->get(route('route-plans.index'))
            ->assertOk()
            ->assertSee($ownRoutePlan->code)
            ->assertDontSee($otherRoutePlan->code);
    }

    public function test_officer_cannot_view_other_officer_route_plan(): void
    {
        $officerUser = $this->createUserWithRole('petugas');
        Officer::factory()->create(['user_id' => $officerUser->id]);
        $otherRoutePlan = app(GreedyRouteService::class)->generate($this->createRunWithDeterministicCoordinates($this->createOfficer()));

        $this->actingAs($officerUser)
            ->get(route('route-plans.show', $otherRoutePlan))
            ->assertForbidden();

        $this->actingAs($officerUser)
            ->get(route('route-plans.map-data', $otherRoutePlan))
            ->assertForbidden();
    }

    public function test_head_can_view_but_cannot_generate_route_plan(): void
    {
        $head = $this->createUserWithRole('kepala_sppg');
        $routePlan = app(GreedyRouteService::class)->generate($this->createRunWithDeterministicCoordinates());

        $this->actingAs($head)
            ->get(route('route-plans.show', $routePlan))
            ->assertOk();

        $this->actingAs($head)
            ->post(route('distribution-runs.route-plan.generate', $routePlan->run))
            ->assertForbidden();
    }

    public function test_route_plan_requires_distribution_destinations(): void
    {
        $officer = $this->createOfficer();
        $schedule = DistributionSchedule::factory()->create([
            'officer_id' => $officer->id,
            'depot_location_id' => Location::factory()->create(['type' => 'depot'])->id,
            'status' => 'scheduled',
        ]);
        $run = DistributionRun::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $officer->id,
        ]);

        $this->actingAs($officer->user)
            ->post(route('distribution-runs.route-plan.generate', $run))
            ->assertSessionHasErrors(['distribution_run_id']);
    }

    private function createRunWithDeterministicCoordinates(?Officer $officer = null): DistributionRun
    {
        $officer ??= $this->createOfficer();
        $depot = Location::factory()->create([
            'name' => 'Depot Test',
            'type' => 'depot',
            'latitude' => 0,
            'longitude' => 0,
        ]);
        $nearestLocation = Location::factory()->create([
            'name' => 'Sekolah Terdekat',
            'latitude' => 0,
            'longitude' => 1,
        ]);
        $farthestLocation = Location::factory()->create([
            'name' => 'Sekolah Terjauh',
            'latitude' => 0,
            'longitude' => 2,
        ]);
        $nearestRecipient = Recipient::factory()->create(['location_id' => $nearestLocation->id, 'portion_count' => 100]);
        $farthestRecipient = Recipient::factory()->create(['location_id' => $farthestLocation->id, 'portion_count' => 125]);
        $schedule = DistributionSchedule::factory()->create([
            'officer_id' => $officer->id,
            'depot_location_id' => $depot->id,
            'status' => 'scheduled',
        ]);
        $nearestScheduleDestination = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $nearestLocation->id,
            'recipient_id' => $nearestRecipient->id,
            'portion_count' => $nearestRecipient->portion_count,
            'sequence_order' => 1,
        ]);
        $farthestScheduleDestination = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $farthestLocation->id,
            'recipient_id' => $farthestRecipient->id,
            'portion_count' => $farthestRecipient->portion_count,
            'sequence_order' => 2,
        ]);
        $run = DistributionRun::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $officer->id,
        ]);

        DistributionRunDestination::factory()->create([
            'distribution_run_id' => $run->id,
            'distribution_schedule_destination_id' => $farthestScheduleDestination->id,
            'location_id' => $farthestLocation->id,
            'recipient_id' => $farthestRecipient->id,
            'planned_portion_count' => $farthestRecipient->portion_count,
            'sequence_order' => 1,
        ]);
        DistributionRunDestination::factory()->create([
            'distribution_run_id' => $run->id,
            'distribution_schedule_destination_id' => $nearestScheduleDestination->id,
            'location_id' => $nearestLocation->id,
            'recipient_id' => $nearestRecipient->id,
            'planned_portion_count' => $nearestRecipient->portion_count,
            'sequence_order' => 2,
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
