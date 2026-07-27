<?php

namespace Tests\Feature;

use App\Models\DistributionSchedule;
use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Officer;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_schedule_list(): void
    {
        $admin = $this->createUserWithRole('admin');
        $schedule = $this->createSchedule();

        $this->actingAs($admin)
            ->get(route('distribution-schedules.index'))
            ->assertOk()
            ->assertSee('Jadwal Distribusi')
            ->assertSee($schedule->code);
    }

    public function test_admin_can_create_schedule_with_destinations_and_total_portions(): void
    {
        $admin = $this->createUserWithRole('admin');
        $officer = $this->createOfficer();
        $depot = Location::factory()->create(['type' => 'depot', 'status' => 'active']);
        [$recipientA, $recipientB] = $this->createRecipientsAtDifferentLocations();

        $this->actingAs($admin)
            ->post(route('distribution-schedules.store'), [
                'code' => 'SCHD-0100',
                'scheduled_date' => '2026-08-01',
                'officer_id' => $officer->id,
                'depot_location_id' => $depot->id,
                'recipient_ids' => [$recipientA->id, $recipientB->id],
                'status' => 'scheduled',
                'notes' => 'Jadwal uji backend',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('distribution_schedules', [
            'code' => 'SCHD-0100',
            'officer_id' => $officer->id,
            'depot_location_id' => $depot->id,
            'total_portions' => $recipientA->portion_count + $recipientB->portion_count,
            'status' => 'scheduled',
        ]);

        $schedule = DistributionSchedule::query()->where('code', 'SCHD-0100')->firstOrFail();

        $this->assertDatabaseHas('distribution_schedule_destinations', [
            'distribution_schedule_id' => $schedule->id,
            'recipient_id' => $recipientA->id,
            'portion_count' => $recipientA->portion_count,
        ]);
        $this->assertCount(2, $schedule->destinations);
    }

    public function test_admin_can_update_schedule_and_resync_destinations(): void
    {
        $admin = $this->createUserWithRole('admin');
        $schedule = $this->createSchedule();
        [$recipientA, $recipientB] = $this->createRecipientsAtDifferentLocations();

        DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $recipientA->location_id,
            'recipient_id' => $recipientA->id,
            'portion_count' => $recipientA->portion_count,
        ]);
        $schedule->recalculateTotalPortions();

        $this->actingAs($admin)
            ->put(route('distribution-schedules.update', $schedule), [
                'code' => 'SCHD-0201',
                'scheduled_date' => '2026-08-02',
                'officer_id' => $schedule->officer_id,
                'depot_location_id' => $schedule->depot_location_id,
                'recipient_ids' => [$recipientB->id],
                'status' => 'draft',
                'notes' => 'Diganti tujuan',
            ])
            ->assertRedirect(route('distribution-schedules.show', $schedule));

        $this->assertDatabaseHas('distribution_schedules', [
            'id' => $schedule->id,
            'code' => 'SCHD-0201',
            'total_portions' => $recipientB->portion_count,
        ]);
        $this->assertDatabaseMissing('distribution_schedule_destinations', [
            'distribution_schedule_id' => $schedule->id,
            'recipient_id' => $recipientA->id,
        ]);
        $this->assertDatabaseHas('distribution_schedule_destinations', [
            'distribution_schedule_id' => $schedule->id,
            'recipient_id' => $recipientB->id,
        ]);
    }

    public function test_admin_can_cancel_schedule(): void
    {
        $admin = $this->createUserWithRole('admin');
        $schedule = $this->createSchedule(['status' => 'scheduled']);

        $this->actingAs($admin)
            ->delete(route('distribution-schedules.destroy', $schedule))
            ->assertRedirect(route('distribution-schedules.index'));

        $this->assertDatabaseHas('distribution_schedules', [
            'id' => $schedule->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_admin_can_add_destination_to_existing_schedule(): void
    {
        $admin = $this->createUserWithRole('admin');
        $schedule = $this->createSchedule();
        [$recipient] = $this->createRecipientsAtDifferentLocations();

        $this->actingAs($admin)
            ->post(route('distribution-schedules.destinations.store', $schedule), [
                'recipient_id' => $recipient->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('distribution_schedule_destinations', [
            'distribution_schedule_id' => $schedule->id,
            'recipient_id' => $recipient->id,
            'portion_count' => $recipient->portion_count,
        ]);
        $this->assertSame($recipient->portion_count, $schedule->fresh()->total_portions);
    }

    public function test_admin_can_remove_destination_from_schedule(): void
    {
        $admin = $this->createUserWithRole('admin');
        $schedule = $this->createSchedule();
        [$recipientA, $recipientB] = $this->createRecipientsAtDifferentLocations();
        $destinationA = DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $recipientA->location_id,
            'recipient_id' => $recipientA->id,
            'portion_count' => $recipientA->portion_count,
        ]);
        DistributionScheduleDestination::factory()->create([
            'distribution_schedule_id' => $schedule->id,
            'location_id' => $recipientB->location_id,
            'recipient_id' => $recipientB->id,
            'portion_count' => $recipientB->portion_count,
        ]);
        $schedule->recalculateTotalPortions();

        $this->actingAs($admin)
            ->delete(route('distribution-schedules.destinations.destroy', [$schedule, $destinationA]))
            ->assertRedirect();

        $this->assertDatabaseMissing('distribution_schedule_destinations', [
            'id' => $destinationA->id,
        ]);
        $this->assertSame($recipientB->portion_count, $schedule->fresh()->total_portions);
    }

    public function test_only_admin_can_manage_schedules(): void
    {
        $petugas = $this->createUserWithRole('petugas');
        $kepala = $this->createUserWithRole('kepala_sppg');

        $this->actingAs($petugas)
            ->get(route('distribution-schedules.index'))
            ->assertForbidden();

        $this->actingAs($kepala)
            ->get(route('distribution-schedules.index'))
            ->assertForbidden();
    }

    public function test_schedule_requires_at_least_one_destination(): void
    {
        $admin = $this->createUserWithRole('admin');
        $scheduleData = $this->validSchedulePayload();

        $this->actingAs($admin)
            ->post(route('distribution-schedules.store'), array_merge($scheduleData, [
                'recipient_ids' => [],
            ]))
            ->assertSessionHasErrors(['recipient_ids']);
    }

    public function test_schedule_rejects_duplicate_destination_locations(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create(['status' => 'active']);
        $recipientA = Recipient::factory()->create(['location_id' => $location->id, 'portion_count' => 100]);
        $recipientB = Recipient::factory()->create(['location_id' => $location->id, 'portion_count' => 120]);

        $this->actingAs($admin)
            ->post(route('distribution-schedules.store'), array_merge($this->validSchedulePayload(), [
                'recipient_ids' => [$recipientA->id, $recipientB->id],
            ]))
            ->assertSessionHasErrors(['recipient_ids']);
    }

    public function test_schedule_rejects_depot_as_destination(): void
    {
        $admin = $this->createUserWithRole('admin');
        $depot = Location::factory()->create(['type' => 'depot', 'status' => 'active']);
        $recipientAtDepot = Recipient::factory()->create([
            'location_id' => $depot->id,
            'portion_count' => 100,
        ]);

        $this->actingAs($admin)
            ->post(route('distribution-schedules.store'), array_merge($this->validSchedulePayload(), [
                'depot_location_id' => $depot->id,
                'recipient_ids' => [$recipientAtDepot->id],
            ]))
            ->assertSessionHasErrors(['recipient_ids']);
    }

    public function test_schedule_requires_active_officer_depot_and_recipients(): void
    {
        $admin = $this->createUserWithRole('admin');
        $inactiveOfficer = $this->createOfficer(['status' => 'inactive']);
        $inactiveDepot = Location::factory()->create(['type' => 'depot', 'status' => 'inactive']);
        $inactiveRecipient = Recipient::factory()->create(['status' => 'inactive']);

        $this->actingAs($admin)
            ->post(route('distribution-schedules.store'), array_merge($this->validSchedulePayload(), [
                'officer_id' => $inactiveOfficer->id,
                'depot_location_id' => $inactiveDepot->id,
                'recipient_ids' => [$inactiveRecipient->id],
            ]))
            ->assertSessionHasErrors(['officer_id', 'depot_location_id', 'recipient_ids.0']);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createSchedule(array $attributes = []): DistributionSchedule
    {
        return DistributionSchedule::factory()->create(array_merge([
            'officer_id' => $this->createOfficer()->id,
            'depot_location_id' => Location::factory()->create(['type' => 'depot'])->id,
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
     * @return array<string, mixed>
     */
    private function validSchedulePayload(): array
    {
        $officer = $this->createOfficer();
        $depot = Location::factory()->create(['type' => 'depot', 'status' => 'active']);
        [$recipient] = $this->createRecipientsAtDifferentLocations();

        return [
            'code' => 'SCHD-VALID',
            'scheduled_date' => '2026-08-01',
            'officer_id' => $officer->id,
            'depot_location_id' => $depot->id,
            'recipient_ids' => [$recipient->id],
            'status' => 'scheduled',
            'notes' => 'Payload valid default',
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
