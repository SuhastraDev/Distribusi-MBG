<?php

namespace Tests\Feature;

use App\Models\DistributionSchedule;
use App\Models\Location;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_location_list(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create([
            'name' => 'SD Dummy Palembang',
        ]);

        $this->actingAs($admin)
            ->get(route('locations.index'))
            ->assertOk()
            ->assertSee('Data Lokasi Distribusi')
            ->assertSee($location->name);
    }

    public function test_admin_can_create_location(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->post(route('locations.store'), [
                'code' => 'LOC-0100',
                'name' => 'SD Negeri 1 Palembang',
                'type' => 'school',
                'address' => 'Jl. Merdeka Palembang',
                'latitude' => -2.990934,
                'longitude' => 104.756554,
                'status' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('locations', [
            'code' => 'LOC-0100',
            'name' => 'SD Negeri 1 Palembang',
            'type' => 'school',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_location(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create([
            'code' => 'LOC-0200',
            'name' => 'Lokasi Lama',
        ]);

        $this->actingAs($admin)
            ->put(route('locations.update', $location), [
                'code' => 'LOC-0201',
                'name' => 'Lokasi Update',
                'type' => 'other',
                'address' => 'Seberang Ulu',
                'latitude' => -3.000001,
                'longitude' => 104.770001,
                'status' => 'active',
            ])
            ->assertRedirect(route('locations.show', $location));

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'code' => 'LOC-0201',
            'name' => 'Lokasi Update',
            'type' => 'other',
        ]);
    }

    public function test_admin_can_deactivate_location(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create(['status' => 'active']);

        $this->actingAs($admin)
            ->delete(route('locations.destroy', $location))
            ->assertRedirect(route('locations.index'));

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'status' => 'inactive',
        ]);

        $this->assertFalse($location->fresh()->isActive());
    }

    public function test_admin_can_permanently_delete_unused_location(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create();

        $this->actingAs($admin)
            ->delete(route('locations.force-delete', $location))
            ->assertRedirect(route('locations.index'));

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    }

    public function test_admin_cannot_permanently_delete_location_still_in_use(): void
    {
        $admin = $this->createUserWithRole('admin');
        $location = Location::factory()->create();
        $recipient = Recipient::factory()->create(['location_id' => $location->id]);

        $this->actingAs($admin)
            ->delete(route('locations.force-delete', $location))
            ->assertRedirect();

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
        $recipient->delete();

        $depotSchedule = DistributionSchedule::factory()->create(['depot_location_id' => $location->id]);
        $this->actingAs($admin)
            ->delete(route('locations.force-delete', $location))
            ->assertRedirect();

        $this->assertDatabaseHas('locations', ['id' => $location->id]);
        $depotSchedule->delete();
    }

    public function test_only_admin_can_manage_locations(): void
    {
        $petugas = $this->createUserWithRole('petugas');
        $kepala = $this->createUserWithRole('kepala_sppg');

        $this->actingAs($petugas)
            ->get(route('locations.index'))
            ->assertForbidden();

        $this->actingAs($kepala)
            ->get(route('locations.index'))
            ->assertForbidden();
    }

    public function test_location_coordinates_are_required_numeric_values(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->post(route('locations.store'), [
                'code' => 'LOC-0300',
                'name' => 'Lokasi Invalid',
                'type' => 'school',
                'latitude' => 'bukan angka',
                'longitude' => null,
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_location_code_must_be_unique(): void
    {
        $admin = $this->createUserWithRole('admin');
        Location::factory()->create(['code' => 'LOC-0400']);

        $this->actingAs($admin)
            ->post(route('locations.store'), [
                'code' => 'LOC-0400',
                'name' => 'Lokasi Duplikat',
                'type' => 'school',
                'latitude' => -2.99,
                'longitude' => 104.75,
                'status' => 'active',
            ])
            ->assertSessionHasErrors(['code']);
    }

    public function test_active_scope_only_returns_active_locations_for_future_schedule_selection(): void
    {
        $activeLocation = Location::factory()->create(['status' => 'active']);
        Location::factory()->create(['status' => 'inactive']);

        $activeLocationIds = Location::query()->active()->pluck('id');

        $this->assertTrue($activeLocationIds->contains($activeLocation->id));
        $this->assertCount(1, $activeLocationIds);
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
