<?php

namespace Tests\Feature;

use App\Models\Officer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleOfficerFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_required_roles_and_demo_users(): void
    {
        $this->seed();

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'display_name' => 'Admin',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'petugas',
            'display_name' => 'Petugas Distribusi',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'kepala_sppg',
            'display_name' => 'Kepala SPPG',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@distribusimbg.test',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('officers', [
            'officer_code' => 'PTG-0001',
            'status' => 'active',
        ]);
    }

    public function test_user_role_and_officer_relationships_are_available(): void
    {
        $role = Role::factory()->create([
            'name' => 'petugas',
            'display_name' => 'Petugas Distribusi',
        ]);

        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => Hash::make('password'),
        ]);

        $officer = Officer::factory()->create([
            'user_id' => $user->id,
            'name' => $user->name,
        ]);

        $this->assertTrue($user->role->is($role));
        $this->assertTrue($user->officer->is($officer));
        $this->assertTrue($officer->user->is($user));
    }
}
