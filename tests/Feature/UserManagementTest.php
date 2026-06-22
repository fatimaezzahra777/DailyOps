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
