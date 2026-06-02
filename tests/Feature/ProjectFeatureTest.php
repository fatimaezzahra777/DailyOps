<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectColumn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_index_keeps_filtered_counts_across_pagination(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Project::create([
            'name' => 'Pending One',
            'status' => 'pending',
        ]);

        Project::create([
            'name' => 'In Progress One',
            'status' => 'in_progress',
        ]);

        Project::create([
            'name' => 'Completed One',
            'status' => 'completed',
        ]);

        for ($i = 1; $i <= 9; $i++) {
            Project::create([
                'name' => "Extra Pending {$i}",
                'status' => 'pending',
            ]);
        }

        $response = $this->get('/projects?page=2');

        $response->assertOk();
        $response->assertSee('Completed One');
        $response->assertSee('In Progress One');
        $response->assertSee('10');
    }

    public function test_project_index_preserves_search_query_in_filters_and_pagination(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        for ($i = 1; $i <= 11; $i++) {
            Project::create([
                'name' => "Alpha {$i}",
                'status' => 'pending',
            ]);
        }

        $response = $this->get('/projects?search=Alpha');

        $response->assertOk();
        $response->assertSee('/projects?search=Alpha&amp;status=pending', false);
        $response->assertSee('/projects?search=Alpha&amp;page=2', false);
    }

    public function test_project_search_form_keeps_the_current_view(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get('/projects/table')
            ->assertOk()
            ->assertSee('action="' . route('projects.table') . '"', false);

        $this->get('/projects/gantt')
            ->assertOk()
            ->assertSee('action="' . route('projects.gantt') . '"', false);

        $this->get('/projects/calendar')
            ->assertOk()
            ->assertSee('action="' . route('projects.calendar') . '"', false);

        $this->get('/projects/reports')
            ->assertOk()
            ->assertSee('action="' . route('projects.reports') . '"', false);
    }

    public function test_guest_is_redirected_from_projects(): void
    {
        $this->get('/projects')->assertRedirect('/login');
        $this->get('/projects/table')->assertRedirect('/login');
        $this->get('/projects/gantt')->assertRedirect('/login');
        $this->get('/projects/calendar')->assertRedirect('/login');
        $this->get('/projects/reports')->assertRedirect('/login');
        $this->post('/projects', [
            'name' => 'Guest Project',
            'status' => 'pending',
        ])->assertRedirect('/login');
    }

    public function test_authenticated_user_can_open_project_views(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Project::create([
            'manager_id' => $user->id,
            'name' => 'Launch Calendar',
            'status' => 'in_progress',
            'end_date' => now()->addWeek(),
        ]);

        $this->get('/projects/table')->assertOk()->assertSee('Tasks - Table view');
        $this->get('/projects/gantt')->assertOk()->assertSee('Projects - Gantt view');
        $this->get('/projects/calendar')->assertOk()->assertSee('Projects - Calendar');
        $this->get('/projects/reports')->assertOk()->assertSee('Projects - Reports');
    }

    public function test_calendar_create_form_is_not_prefilled_from_an_upcoming_project(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Project::create([
            'manager_id' => $user->id,
            'name' => 'Upcoming Delivery',
            'description' => 'Visible in the calendar agenda.',
            'status' => 'in_progress',
            'end_date' => now()->addWeek(),
        ]);

        $response = $this->get('/projects/calendar');

        $response->assertOk();
        $response->assertSee('Upcoming Delivery');
        $this->assertMatchesRegularExpression(
            '/id="create-project-name"[^>]*value=""[^>]*data-field-default=""/',
            $response->getContent()
        );
    }

    public function test_project_creator_becomes_project_manager(): void
    {
        $user = User::factory()->create(['name' => 'Project Manager']);

        $this->actingAs($user)
            ->post('/projects', [
                'name' => 'Managed Project',
                'status' => 'pending',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Managed Project',
            'manager_id' => $user->id,
            'assigned_to' => 'Project Manager',
        ]);
    }

    public function test_member_only_sees_and_manages_own_projects(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownProject = Project::create([
            'manager_id' => $owner->id,
            'name' => 'Owner Project',
            'status' => 'pending',
        ]);
        $otherProject = Project::create([
            'manager_id' => $otherUser->id,
            'name' => 'Other Project',
            'status' => 'pending',
        ]);

        $this->actingAs($owner)
            ->get('/projects')
            ->assertOk()
            ->assertSee('Owner Project')
            ->assertDontSee('Other Project');

        $this->actingAs($owner)
            ->get(route('projects.show', $ownProject))
            ->assertOk();

        $this->actingAs($owner)
            ->get(route('projects.show', $otherProject))
            ->assertForbidden();
    }

    public function test_member_sees_projects_assigned_to_them(): void
    {
        $member = User::factory()->create([
            'name' => 'Assigned Member',
            'email' => 'assigned@example.com',
        ]);

        $assignedByName = Project::create([
            'name' => 'Assigned By Name',
            'status' => 'pending',
            'assigned_to' => 'Assigned Member',
        ]);

        $assignedByEmail = Project::create([
            'name' => 'Assigned By Email',
            'status' => 'pending',
            'assigned_to' => 'assigned@example.com',
        ]);

        Project::create([
            'name' => 'Hidden Project',
            'status' => 'pending',
            'assigned_to' => 'Someone Else',
        ]);

        $this->actingAs($member)
            ->get('/projects')
            ->assertOk()
            ->assertSee('Assigned By Name')
            ->assertSee('Assigned By Email')
            ->assertDontSee('Hidden Project');

        $this->actingAs($member)
            ->get(route('projects.show', $assignedByName))
            ->assertOk();

        $this->actingAs($member)
            ->get(route('projects.show', $assignedByEmail))
            ->assertOk();
    }

    public function test_user_can_add_a_board_column_and_create_project_inside_it(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('projects.columns.store'), [
                'name' => 'Blocked',
            ])
            ->assertRedirect(route('projects.index'));

        $column = ProjectColumn::where('name', 'Blocked')->firstOrFail();

        $this->actingAs($user)
            ->post('/projects', [
                'name' => 'Waiting Approval',
                'status' => 'pending',
                'column_id' => $column->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Waiting Approval',
            'column_id' => $column->id,
            'manager_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get('/projects')
            ->assertOk()
            ->assertSee('Blocked')
            ->assertSee('Waiting Approval');
    }

    public function test_user_can_move_project_between_status_columns(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Movable Project',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->patchJson(route('projects.move', $project), [
                'status' => 'completed',
                'column_id' => null,
            ])
            ->assertOk();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'completed',
            'column_id' => null,
        ]);
    }

    public function test_user_can_move_project_to_custom_column(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $column = ProjectColumn::create([
            'name' => 'QA',
            'position' => 1,
        ]);
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Custom Column Project',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->patchJson(route('projects.move', $project), [
                'column_id' => $column->id,
            ])
            ->assertOk();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'column_id' => $column->id,
        ]);
    }

    public function test_database_seeder_can_run_twice_without_duplicate_users(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => env('ADMIN_EMAIL', 'soft7art@dailyops.com'),
            'role' => 'admin',
        ]);
        $this->assertDatabaseCount('projects', 9);
    }
}
