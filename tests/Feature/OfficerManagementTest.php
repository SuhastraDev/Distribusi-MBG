<?php

namespace Tests\Feature;

use App\Models\DistributionSchedule;
use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OfficerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_officer_list(): void
    {
        $admin = $this->createUserWithRole('admin');
        $officer = Officer::factory()->create([
            'name' => 'Petugas Demo',
            'user_id' => $this->createUserWithRole('petugas')->id,
        ]);

        $this->actingAs($admin)
            ->get(route('officers.index'))
            ->assertOk()
            ->assertSee('Data Petugas Distribusi')
            ->assertSee($officer->name);
    }

    public function test_admin_can_create_officer_and_login_user(): void
    {
        $admin = $this->createUserWithRole('admin');
        Role::factory()->create(['name' => 'petugas', 'display_name' => 'Petugas Distribusi']);

        $this->actingAs($admin)
            ->post(route('officers.store'), [
                'officer_code' => 'PTG-0100',
                'name' => 'Petugas Baru',
                'email' => 'petugas-baru@example.test',
                'phone' => '08123456789',
                'address' => 'Palembang',
                'status' => 'active',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('officers', [
            'officer_code' => 'PTG-0100',
            'name' => 'Petugas Baru',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'petugas-baru@example.test',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_officer_without_changing_password(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('petugas', [
            'email' => 'petugas-lama@example.test',
            'password' => Hash::make('old-password'),
        ]);
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'officer_code' => 'PTG-0200',
            'name' => 'Petugas Lama',
        ]);

        $this->actingAs($admin)
            ->put(route('officers.update', $officer), [
                'officer_code' => 'PTG-0201',
                'name' => 'Petugas Update',
                'email' => 'petugas-update@example.test',
                'phone' => '089999999',
                'address' => 'Jakabaring',
                'status' => 'active',
            ])
            ->assertRedirect(route('officers.show', $officer));

        $this->assertDatabaseHas('officers', [
            'id' => $officer->id,
            'officer_code' => 'PTG-0201',
            'name' => 'Petugas Update',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'petugas-update@example.test',
        ]);

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_admin_can_deactivate_officer(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('petugas');
        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->delete(route('officers.destroy', $officer))
            ->assertRedirect(route('officers.index'));

        $this->assertDatabaseHas('officers', [
            'id' => $officer->id,
            'status' => 'inactive',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
        ]);

        $this->assertFalse($officer->fresh()->isActive());
    }

    public function test_admin_can_permanently_delete_unused_officer(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('petugas');
        $officer = Officer::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin)
            ->delete(route('officers.force-delete', $officer))
            ->assertRedirect(route('officers.index'));

        $this->assertDatabaseMissing('officers', ['id' => $officer->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_permanently_delete_officer_with_schedule_history(): void
    {
        $admin = $this->createUserWithRole('admin');
        $user = $this->createUserWithRole('petugas');
        $officer = Officer::factory()->create(['user_id' => $user->id]);
        DistributionSchedule::factory()->create(['officer_id' => $officer->id]);

        $this->actingAs($admin)
            ->delete(route('officers.force-delete', $officer))
            ->assertRedirect();

        $this->assertDatabaseHas('officers', ['id' => $officer->id]);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_only_admin_can_manage_officers(): void
    {
        $petugas = $this->createUserWithRole('petugas');
        $kepala = $this->createUserWithRole('kepala_sppg');

        $this->actingAs($petugas)
            ->get(route('officers.index'))
            ->assertForbidden();

        $this->actingAs($kepala)
            ->get(route('officers.index'))
            ->assertForbidden();
    }

    public function test_officer_email_and_code_must_be_unique(): void
    {
        $admin = $this->createUserWithRole('admin');
        Role::factory()->create(['name' => 'petugas', 'display_name' => 'Petugas Distribusi']);
        $existingUser = $this->createUserWithRole('petugas', ['email' => 'duplikat@example.test']);
        Officer::factory()->create([
            'user_id' => $existingUser->id,
            'officer_code' => 'PTG-0300',
        ]);

        $this->actingAs($admin)
            ->post(route('officers.store'), [
                'officer_code' => 'PTG-0300',
                'name' => 'Petugas Duplikat',
                'email' => 'duplikat@example.test',
                'status' => 'active',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertSessionHasErrors(['officer_code', 'email']);
    }

    public function test_active_scope_excludes_inactive_officers_from_future_selection(): void
    {
        $activeUser = $this->createUserWithRole('petugas', ['status' => 'active']);
        $inactiveUser = $this->createUserWithRole('petugas', ['status' => 'inactive']);
        $activeOfficer = Officer::factory()->create([
            'user_id' => $activeUser->id,
            'status' => 'active',
        ]);
        Officer::factory()->create([
            'user_id' => $inactiveUser->id,
            'status' => 'inactive',
        ]);

        $activeOfficerIds = Officer::query()->active()->pluck('id');

        $this->assertTrue($activeOfficerIds->contains($activeOfficer->id));
        $this->assertCount(1, $activeOfficerIds);
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
