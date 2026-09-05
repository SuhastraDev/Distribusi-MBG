<?php

namespace Tests\Feature;

use App\Models\DistributionScheduleDestination;
use App\Models\Location;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_recipient_list(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create(['name' => 'SD Demo']);
        $recipient = Recipient::factory()->create([
            'location_id' => $location->id,
            'name' => 'Siswa SD Demo',
        ]);

        $this->actingAs($admin)
            ->get(route('recipients.index'))
            ->assertOk()
            ->assertSee('Data Penerima MBG')
            ->assertSee($recipient->name)
            ->assertSee($location->name);
    }

    public function test_admin_can_create_recipient_for_active_location(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->post(route('recipients.store'), [
                'location_id' => $location->id,
                'code' => 'RCV-0100',
                'name' => 'Siswa Kelas 1 SD',
                'portion_count' => 120,
                'notes' => 'Penerima makan siang',
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('recipients', [
            'location_id' => $location->id,
            'code' => 'RCV-0100',
            'name' => 'Siswa Kelas 1 SD',
            'portion_count' => 120,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_recipient(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create(['status' => 'active']);
        $recipient = Recipient::factory()->create([
            'location_id' => $location->id,
            'code' => 'RCV-0200',
            'name' => 'Penerima Lama',
        ]);

        $this->actingAs($admin)
            ->put(route('recipients.update', $recipient), [
                'location_id' => $location->id,
                'code' => 'RCV-0201',
                'name' => 'Penerima Update',
                'portion_count' => 150,
                'notes' => 'Update jumlah porsi',
                'status' => 'active',
            ])
            ->assertRedirect(route('recipients.show', $recipient));

        $this->assertDatabaseHas('recipients', [
            'id' => $recipient->id,
            'code' => 'RCV-0201',
            'name' => 'Penerima Update',
            'portion_count' => 150,
        ]);
    }

    public function test_admin_can_deactivate_recipient(): void
    {
        $admin = $this->createUserWithRole('admin');
        $recipient = Recipient::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->delete(route('recipients.destroy', $recipient))
            ->assertRedirect(route('recipients.index'));

        $this->assertDatabaseHas('recipients', [
            'id' => $recipient->id,
            'status' => 'inactive',
        ]);

        $this->assertFalse($recipient->fresh()->isActive());
    }

    public function test_admin_can_permanently_delete_unused_recipient(): void
    {
        $admin = $this->createUserWithRole('admin');
        $recipient = Recipient::factory()->create();

        $this->actingAs($admin)
            ->delete(route('recipients.force-delete', $recipient))
            ->assertRedirect(route('recipients.index'));

        $this->assertDatabaseMissing('recipients', ['id' => $recipient->id]);
    }

    public function test_admin_cannot_permanently_delete_recipient_with_schedule_history(): void
    {
        $admin = $this->createUserWithRole('admin');
        $recipient = Recipient::factory()->create();
        DistributionScheduleDestination::factory()->create(['recipient_id' => $recipient->id]);

        $this->actingAs($admin)
            ->delete(route('recipients.force-delete', $recipient))
            ->assertRedirect();

        $this->assertDatabaseHas('recipients', ['id' => $recipient->id]);
    }

    public function test_only_admin_can_manage_recipients(): void
    {
        $petugas = $this->createUserWithRole('petugas');
        $kepala = $this->createUserWithRole('kepala_sppg');

        $this->actingAs($petugas)
            ->get(route('recipients.index'))
            ->assertForbidden();

        $this->actingAs($kepala)
            ->get(route('recipients.index'))
            ->assertForbidden();
    }

    public function test_portion_count_must_be_greater_than_zero(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->post(route('recipients.store'), [
                'location_id' => $location->id,
                'code' => 'RCV-0300',
                'name' => 'Penerima Invalid',
                'portion_count' => 0,
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['portion_count']);
    }

    public function test_recipient_must_be_connected_to_active_location(): void
    {
        $admin = $this->createUserWithRole('admin');
        $inactiveLocation = Location::factory()->create(['status' => 'inactive']);

        $this->actingAs($admin)
            ->post(route('recipients.store'), [
                'location_id' => $inactiveLocation->id,
                'code' => 'RCV-0400',
                'name' => 'Penerima Lokasi Nonaktif',
                'portion_count' => 100,
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['location_id']);
    }

    public function test_recipient_code_must_be_unique(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create(['status' => 'active']);
        Recipient::factory()->create([
            'location_id' => $location->id,
            'code' => 'RCV-0500',
        ]);

        $this->actingAs($admin)
            ->post(route('recipients.store'), [
                'location_id' => $location->id,
                'code' => 'RCV-0500',
                'name' => 'Penerima Duplikat',
                'portion_count' => 100,
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['code']);
    }

    public function test_active_scope_only_returns_active_recipients_on_active_locations(): void
    {
        $activeLocation = Location::factory()->create(['status' => 'active']);
        $inactiveLocation = Location::factory()->create(['status' => 'inactive']);
        $activeRecipient = Recipient::factory()->create([
            'location_id' => $activeLocation->id,
            'status' => 'active',
        ]);
        Recipient::factory()->create([
            'location_id' => $activeLocation->id,
            'status' => 'inactive',
        ]);
        Recipient::factory()->create([
            'location_id' => $inactiveLocation->id,
            'status' => 'active',
        ]);

        $activeRecipientIds = Recipient::query()->active()->pluck('id');

        $this->assertTrue($activeRecipientIds->contains($activeRecipient->id));
        $this->assertCount(1, $activeRecipientIds);
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
