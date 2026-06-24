<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_created_user_can_login_immediately(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/users', [
                'name' => 'Member Ready',
                'email' => 'member-ready@example.com',
                'birth_date' => '1992-04-12',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'member',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'member-ready@example.com',
            'role' => 'member',
            'birth_date' => '1992-04-12 00:00:00',
        ]);

        $createdUser = User::where('email', 'member-ready@example.com')->firstOrFail();

        $this->assertNotNull($createdUser->email_verified_at);

        auth()->logout();

        $this->post('/login', [
            'email' => 'member-ready@example.com',
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($createdUser);
    }

    public function test_admin_can_create_another_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Second Admin',
                'email' => 'second-admin@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'admin',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'second-admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_change_a_user_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('users.update', $user), [
                'name' => $user->name,
                'email' => $user->email,
                'password' => '',
                'password_confirmation' => '',
                'role' => 'member',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'member',
        ]);
    }

    public function test_admin_cannot_change_their_own_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'password' => '',
                'password_confirmation' => '',
                'role' => 'member',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_role_selector_is_hidden_when_admin_edits_their_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users.edit', $admin))
            ->assertOk()
            ->assertDontSee('Role utilisateur')
            ->assertDontSee('name="role"', false);
    }

    public function test_member_cannot_access_user_role_management(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $otherUser = User::factory()->create();

        $this->actingAs($member)
            ->get(route('users.edit', $otherUser))
            ->assertForbidden();

        $this->actingAs($member)
            ->put(route('users.update', $otherUser), [
                'name' => $otherUser->name,
                'email' => $otherUser->email,
                'role' => 'admin',
            ])
            ->assertForbidden();
    }

    public function test_user_role_must_be_admin_or_member(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('users.store'), [
                'name' => 'Invalid Role',
                'email' => 'invalid-role@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => 'super-admin',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', [
            'email' => 'invalid-role@example.com',
        ]);
    }

    public function test_user_details_show_relevant_projects_and_admin_sees_all_projects(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create([
            'name' => 'Assigned Member',
            'email' => 'assigned-member@example.com',
        ]);
        $other = User::factory()->create();

        $managedProject = Project::create([
            'manager_id' => $member->id,
            'name' => 'Managed Project',
            'status' => 'pending',
        ]);
        $assignedProject = Project::create([
            'manager_id' => $other->id,
            'name' => 'Assigned Project',
            'status' => 'in_progress',
            'assigned_to' => 'assigned-member@example.com',
        ]);
        Project::create([
            'manager_id' => $other->id,
            'name' => 'Admin Visible Project',
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->get(route('users.show', $member))
            ->assertOk()
            ->assertSee('Managed Project')
            ->assertSee('Assigned Project')
            ->assertDontSee('Admin Visible Project');

        $this->actingAs($admin)
            ->get(route('users.show', $admin))
            ->assertOk()
            ->assertSee('Managed Project')
            ->assertSee('Assigned Project')
            ->assertSee('Admin Visible Project');
    }
}
