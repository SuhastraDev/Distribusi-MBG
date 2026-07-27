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
use App\Models\RoutePlanStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_read_dashboard_summary_api(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRunWithRoute('completed');

        $this->actingAs($admin)
            ->get(route('api.frontend.dashboard-summary'))
            ->assertOk()
            ->assertJsonPath('distributions.total', 1)
            ->assertJsonPath('distributions.completed', 1)
            ->assertJsonPath('routes.total', 1)
            ->assertJsonPath('latest_distributions.0.code', $run->code);
    }

    public function test_distribution_runs_api_supports_status_filter_and_pagination_meta(): void
    {
        $admin = $this->createUserWithRole('admin');
        $completedRun = $this->createRunWithRoute('completed');
        $this->createRunWithRoute('ready');

        $this->actingAs($admin)
            ->get(route('api.frontend.distribution-runs.index', ['status' => 'completed']))
            ->assertOk()
            ->assertJsonPath('data.0.code', $completedRun->code)
            ->assertJsonPath('data.0.status', 'completed')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_distribution_run_detail_api_returns_destinations(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRunWithRoute('completed');

        $this->actingAs($admin)
            ->get(route('api.frontend.distribution-runs.show', $run))
            ->assertOk()
            ->assertJsonPath('code', $run->code)
            ->assertJsonPath('destinations.0.status', 'delivered')
            ->assertJsonPath('destinations.0.location.name', 'Sekolah API');
    }

    public function test_route_map_api_returns_route_steps(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRunWithRoute('completed');
        $routePlan = $run->routePlan;

        $this->actingAs($admin)
            ->get(route('api.frontend.route-plans.map', $routePlan))
            ->assertOk()
            ->assertJsonPath('route.code', $routePlan->code)
            ->assertJsonPath('distribution.code', $run->code)
            ->assertJsonPath('steps.0.type', 'start')
            ->assertJsonPath('steps.1.location.name', 'Sekolah API');
    }

    public function test_report_summary_api_is_limited_to_admin_and_head(): void
    {
        $admin = $this->createUserWithRole('admin');
        $head = $this->createUserWithRole('kepala_sppg');
        $officerUser = $this->createUserWithRole('petugas');
        $this->createRunWithRoute('completed');

        $this->actingAs($admin)
            ->get(route('api.frontend.reports.distributions.summary'))
            ->assertOk()
            ->assertJsonPath('total_runs', 1)
            ->assertJsonPath('completed_runs', 1);

        $this->actingAs($head)
            ->get(route('api.frontend.reports.distributions.summary'))
            ->assertOk();

        $this->actingAs($officerUser)
            ->get(route('api.frontend.reports.distributions.summary'))
            ->assertForbidden();
    }

    public function test_frontend_api_requires_authentication(): void
    {
        $this->get(route('api.frontend.dashboard-summary'))
            ->assertUnauthorized();
    }

    private function createRunWithRoute(string $status): DistributionRun
    {
        $officer = $this->createOfficer();
        $depot = Location::factory()->create([
            'name' => 'Depot API',
            'type' => 'depot',
            'latitude' => -3.02515,
            'longitude' => 104.77925,
        ]);
        $location = Location::factory()->create([
            'name' => 'Sekolah API',
            'latitude' => -2.99598,
            'longitude' => 104.76477,
        ]);
        $recipient = Recipient::factory()->create(['location_id' => $location->id, 'portion_count' => 100]);
        $schedule = DistributionSchedule::factory()->create([
            'officer_id' => $officer->id,
            'depot_location_id' => $depot->id,
            'scheduled_date' => '2026-08-01',
            'status' => 'scheduled',
        ]);
        $scheduleDestination = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $location->id,
            'recipient_id' => $recipient->id,
            'portion_count' => 100,
        ]);
        $run = DistributionRun::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $officer->id,
            'status' => $status,
        ]);
        $runDestination = DistributionRunDestination::factory()->create([
            'distribution_run_id' => $run->id,
            'distribution_schedule_destination_id' => $scheduleDestination->id,
            'location_id' => $location->id,
            'recipient_id' => $recipient->id,
            'planned_portion_count' => 100,
            'delivered_portion_count' => $status === 'completed' ? 95 : null,
            'status' => $status === 'completed' ? 'delivered' : 'pending',
        ]);
        $routePlan = RoutePlan::factory()->create([
            'distribution_run_id' => $run->id,
            'total_distance_km' => 4.25,
            'total_estimated_minutes' => 11,
        ]);
        RoutePlanStep::factory()->create([
            'route_plan_id' => $routePlan->id,
            'location_id' => $depot->id,
            'step_order' => 1,
            'step_type' => 'start',
        ]);
        RoutePlanStep::factory()->create([
            'route_plan_id' => $routePlan->id,
            'distribution_run_destination_id' => $runDestination->id,
            'location_id' => $location->id,
            'step_order' => 2,
            'step_type' => 'destination',
            'distance_from_previous_km' => 4.25,
            'cumulative_distance_km' => 4.25,
        ]);

        return $run->fresh(['routePlan']);
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
