<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Login Sistem Distribusi MBG');
    }

    public function test_active_user_can_login_and_is_redirected_to_role_dashboard(): void
    {
        $role = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'email' => 'admin@example.test',
            'status' => 'active',
            'last_login_at' => null,
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login.store'), [
            'email' => 'admin@example.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);

        $this->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_inactive_user_cannot_login(): void
    {
        $role = Role::factory()->create(['name' => 'admin']);
        User::factory()->create([
            'role_id' => $role->id,
            'email' => 'inactive@example.test',
            'status' => 'inactive',
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login.store'), [
            'email' => 'inactive@example.test',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_role_middleware_blocks_access_to_other_role_dashboard(): void
    {
        $role = Role::factory()->create(['name' => 'petugas']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('officer.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Petugas Distribusi');
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $role = Role::factory()->create(['name' => 'kepala_sppg']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_user_can_logout(): void
    {
        $role = Role::factory()->create(['name' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
