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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionRunManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_distribution_run_list(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun();

        $this->actingAs($admin)
            ->get(route('distribution-runs.index'))
            ->assertOk()
            ->assertSee('Distribusi Aktual')
            ->assertSee($run->code);
    }

    public function test_admin_can_create_distribution_run_from_scheduled_schedule(): void
    {
        $admin = $this->createUserWithRole('admin');
        $schedule = $this->createScheduleWithDestinations();

        $this->actingAs($admin)
            ->post(route('distribution-runs.store'), [
                'distribution_schedule_id' => $schedule->id,
                'notes' => 'Distribusi aktual test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('distribution_runs', [
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $schedule->officer_id,
            'status' => 'ready',
        ]);

        $run = DistributionRun::query()
            ->where('distribution_schedule_id', $schedule->id)
            ->firstOrFail();

        $this->assertCount(2, $run->destinations);
        $this->assertDatabaseHas('distribution_run_destinations', [
            'distribution_run_id' => $run->id,
            'status' => 'pending',
        ]);
    }

    public function test_schedule_can_only_have_one_distribution_run(): void
    {
        $admin = $this->createUserWithRole('admin');
        $schedule = $this->createScheduleWithDestinations();
        DistributionRun::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $schedule->officer_id,
        ]);

        $this->actingAs($admin)
            ->post(route('distribution-runs.store'), [
                'distribution_schedule_id' => $schedule->id,
            ])
            ->assertSessionHasErrors(['distribution_schedule_id']);
    }

    public function test_officer_can_start_own_distribution_run(): void
    {
        $officerUser = $this->createUserWithRole('petugas');
        $officer = Officer::factory()->create(['user_id' => $officerUser->id]);
        $run = $this->createRun(['officer_id' => $officer->id]);

        $this->actingAs($officerUser)
            ->post(route('distribution-runs.start', $run))
            ->assertRedirect();

        $this->assertDatabaseHas('distribution_runs', [
            'id' => $run->id,
            'status' => 'in_progress',
        ]);
        $this->assertNotNull($run->fresh()->started_at);
    }

    public function test_officer_cannot_manage_other_officer_distribution_run(): void
    {
        $officerUser = $this->createUserWithRole('petugas');
        Officer::factory()->create(['user_id' => $officerUser->id]);
        $otherOfficer = $this->createOfficer();
        $run = $this->createRun(['officer_id' => $otherOfficer->id]);

        $this->actingAs($officerUser)
            ->post(route('distribution-runs.start', $run))
            ->assertForbidden();
    }

    public function test_admin_can_update_destination_as_arrived_or_delivered(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun(['status' => 'in_progress']);
        $destination = $this->createRunDestination($run, ['planned_portion_count' => 120]);

        $this->actingAs($admin)
            ->put(route('distribution-runs.destinations.update', [$run, $destination]), [
                'status' => 'delivered',
                'delivered_portion_count' => 110,
                'proof_notes' => 'Diterima guru piket',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('distribution_run_destinations', [
            'id' => $destination->id,
            'status' => 'delivered',
            'delivered_portion_count' => 110,
            'proof_notes' => 'Diterima guru piket',
        ]);
        $this->assertNotNull($destination->fresh()->delivered_at);
    }

    public function test_delivered_portion_cannot_exceed_planned_portion(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun(['status' => 'in_progress']);
        $destination = $this->createRunDestination($run, ['planned_portion_count' => 100]);

        $this->actingAs($admin)
            ->put(route('distribution-runs.destinations.update', [$run, $destination]), [
                'status' => 'delivered',
                'delivered_portion_count' => 101,
            ])
            ->assertSessionHasErrors(['delivered_portion_count']);
    }

    public function test_distribution_run_can_complete_when_all_destinations_are_closed(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun(['status' => 'in_progress']);
        $this->createRunDestination($run, ['status' => 'delivered', 'delivered_portion_count' => 100]);
        $this->createRunDestination($run, ['status' => 'skipped']);

        $this->actingAs($admin)
            ->post(route('distribution-runs.complete', $run))
            ->assertRedirect();

        $this->assertDatabaseHas('distribution_runs', [
            'id' => $run->id,
            'status' => 'completed',
        ]);
        $this->assertNotNull($run->fresh()->completed_at);
    }

    public function test_distribution_run_cannot_complete_with_pending_destinations(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun(['status' => 'in_progress']);
        $this->createRunDestination($run, ['status' => 'pending']);

        $this->actingAs($admin)
            ->post(route('distribution-runs.complete', $run))
            ->assertStatus(422);
    }

    public function test_admin_can_cancel_distribution_run_before_completed(): void
    {
        $admin = $this->createUserWithRole('admin');
        $run = $this->createRun(['status' => 'ready']);

        $this->actingAs($admin)
            ->post(route('distribution-runs.cancel', $run))
            ->assertRedirect();

        $this->assertDatabaseHas('distribution_runs', [
            'id' => $run->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_head_can_view_but_cannot_start_distribution_run(): void
    {
        $head = $this->createUserWithRole('kepala_sppg');
        $run = $this->createRun();

        $this->actingAs($head)
            ->get(route('distribution-runs.show', $run))
            ->assertOk()
            ->assertSee($run->code);

        $this->actingAs($head)
            ->post(route('distribution-runs.start', $run))
            ->assertForbidden();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRun(array $attributes = []): DistributionRun
    {
        $officerId = $attributes['officer_id'] ?? $this->createOfficer()->id;
        $schedule = $this->createScheduleWithDestinations([
            'officer_id' => $officerId,
        ]);

        return DistributionRun::factory()->create(array_merge([
            'distribution_schedule_id' => $schedule->id,
            'officer_id' => $officerId,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createScheduleWithDestinations(array $attributes = []): DistributionSchedule
    {
        $officerId = $attributes['officer_id'] ?? $this->createOfficer()->id;
        $schedule = DistributionSchedule::factory()->create(array_merge([
            'officer_id' => $officerId,
            'depot_location_id' => Location::factory()->create(['type' => 'depot'])->id,
            'status' => 'scheduled',
        ], $attributes));
        [$recipientA, $recipientB] = $this->createRecipientsAtDifferentLocations();

        DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $recipientA->location_id,
            'recipient_id' => $recipientA->id,
            'portion_count' => $recipientA->portion_count,
            'sequence_order' => 1,
        ]);
        DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $recipientB->location_id,
            'recipient_id' => $recipientB->id,
            'portion_count' => $recipientB->portion_count,
            'sequence_order' => 2,
        ]);
        $schedule->recalculateTotalPortions();

        return $schedule;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createRunDestination(DistributionRun $run, array $attributes = []): DistributionRunDestination
    {
        $recipient = Recipient::factory()->create([
            'location_id' => Location::factory()->create()->id,
            'portion_count' => $attributes['planned_portion_count'] ?? 100,
        ]);
        $scheduleDestination = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $run->distribution_schedule_id,
            'location_id' => $recipient->location_id,
            'recipient_id' => $recipient->id,
            'portion_count' => $recipient->portion_count,
        ]);

        return DistributionRunDestination::factory()->create(array_merge([
            'distribution_run_id' => $run->id,
            'distribution_schedule_destination_id' => $scheduleDestination->id,
            'location_id' => $recipient->location_id,
            'recipient_id' => $recipient->id,
            'planned_portion_count' => $recipient->portion_count,
        ], $attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createOfficer(array $attributes = []): Officer
    {
        return Officer::factory()->create(array_merge([
            'user_id' => $this->createUserWithRole('petugas')->id,
            'status' => 'active',
        ], $attributes));
    }

    /**
     * @return array<int, Recipient>
     */
    private function createRecipientsAtDifferentLocations(): array
    {
        $locationA = Location::factory()->create(['status' => 'active']);
        $locationB = Location::factory()->create(['status' => 'active']);

        return [
            Recipient::factory()->create(['location_id' => $locationA->id, 'portion_count' => 100]),
            Recipient::factory()->create(['location_id' => $locationB->id, 'portion_count' => 125]),
        ];
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
