<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Mail\ProjectInvitationMail;
use App\Models\ProjectInvitation;
use App\Models\ProjectColumn;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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

    public function test_project_navigation_keeps_active_search_filters(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $query = [
            'search' => 'Launch',
            'status' => 'in_progress',
        ];

        $this->get('/projects?search=Launch&status=in_progress')
            ->assertOk()
            ->assertSee(route('projects.table', $query))
            ->assertSee(route('projects.gantt', $query));

        $this->get('/projects/gantt?search=Launch&status=in_progress')
            ->assertOk()
            ->assertSee(route('projects.index', $query))
            ->assertSee(route('projects.table', $query));
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
                'company' => 'softart',
                'status' => 'pending',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Managed Project',
            'company' => 'softart',
            'manager_id' => $user->id,
            'assigned_to' => null,
        ]);
    }

    public function test_project_company_selector_is_visible_and_company_is_saved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('projects.create'))
            ->assertOk()
            ->assertSee('images/companies/softart.png')
            ->assertSee('images/companies/company-name.png')
            ->assertSee('name="company"', false);

        $this->actingAs($user)
            ->post(route('projects.store'), [
                'name' => 'SoftArt Website',
                'company' => 'softart',
                'status' => 'pending',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'SoftArt Website',
            'company' => 'softart',
        ]);
    }

    public function test_project_company_must_be_one_of_the_available_companies(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('projects.store'), [
                'name' => 'Invalid Company Project',
                'company' => 'unknown_company',
                'status' => 'pending',
            ])
            ->assertSessionHasErrors('company', errorBag: 'createProject');

        $this->assertDatabaseMissing('projects', [
            'name' => 'Invalid Company Project',
        ]);
    }

    public function test_project_company_logo_is_displayed_in_table_board_and_project_information(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Branded Project',
            'company' => 'softart',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('projects.table'))
            ->assertOk()
            ->assertSee('Entreprise')
            ->assertDontSee('<th>Column</th>', false)
            ->assertSee('project-company-circle', false)
            ->assertSee('images/companies/softart.png');

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('project-company-circle-small', false);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Project information')
            ->assertSee('project-company-circle-large', false)
            ->assertSee('SoftArt');
    }

    public function test_project_table_can_be_filtered_by_company(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        Project::create([
            'name' => 'SoftArt Project',
            'company' => 'softart',
            'status' => 'pending',
        ]);
        Project::create([
            'name' => 'Company Name Project',
            'company' => 'company_name',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('projects.table', ['company' => 'softart']))
            ->assertOk()
            ->assertSee('SoftArt Project')
            ->assertDontSee('Company Name Project')
            ->assertSee('<option value="softart" selected>SoftArt</option>', false)
            ->assertSee(route('projects.index', ['company' => 'softart']));
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

    public function test_project_manager_can_invite_collaborator_by_email(): void
    {
        Mail::fake();

        $manager = User::factory()->create();
        $collaborator = User::factory()->create([
            'email' => 'collaborator@example.com',
        ]);
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Collaborative Project',
            'status' => 'pending',
        ]);

        $this->actingAs($manager)
            ->post(route('project-invitations.store', $project), [
                'email' => 'collaborator@example.com',
            ])
            ->assertRedirect();

        $invitation = ProjectInvitation::firstOrFail();

        $this->assertSame('pending', $invitation->status);
        $this->assertSame($project->id, $invitation->project_id);
        $this->assertSame($collaborator->id, $invitation->user_id);
        Mail::assertSent(ProjectInvitationMail::class, fn (ProjectInvitationMail $mail) => $mail->invitation->is($invitation));
    }

    public function test_invited_user_accepts_invitation_and_becomes_project_collaborator(): void
    {
        $manager = User::factory()->create();
        $collaborator = User::factory()->create([
            'email' => 'collaborator@example.com',
        ]);
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Accepted Collaboration',
            'status' => 'pending',
        ]);
        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'invited_by' => $manager->id,
            'user_id' => $collaborator->id,
            'email' => 'collaborator@example.com',
            'token' => 'test-token',
            'status' => 'pending',
        ]);
        $acceptUrl = URL::temporarySignedRoute(
            'project-invitations.accept',
            now()->addDay(),
            $invitation,
        );

        $this->actingAs($collaborator)
            ->get($acceptUrl)
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('project_user', [
            'project_id' => $project->id,
            'user_id' => $collaborator->id,
            'invited_by' => $manager->id,
        ]);
        $this->assertDatabaseHas('project_invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);

        $this->actingAs($collaborator)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Accepted Collaboration')
            ->assertSee($collaborator->name)
            ->assertSee($collaborator->email);
    }

    public function test_project_create_modal_keeps_column_hidden(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        ProjectColumn::create([
            'user_id' => $user->id,
            'name' => 'Client QA',
            'position' => 1,
        ]);

        $response = $this->actingAs($user)
            ->get('/projects');

        $response->assertOk();
        $response->assertDontSee('for="create-project-column-id"', false);
        $response->assertSee('id="create-project-column-id" name="create_column_id" type="hidden"', false);
    }

    public function test_project_show_create_task_modal_is_not_prefilled_from_existing_tasks(): void
    {
        $manager = User::factory()->create(['role' => 'admin']);
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Task Modal Project',
            'status' => 'pending',
        ]);
        $project->tasks()->create([
            'title' => 'Existing Task',
            'description' => 'Should not prefill create modal',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('projects.show', $project));

        $response->assertOk();
        $this->assertMatchesRegularExpression(
            '/id="create-task-title"[^>]*value=""[^>]*data-field-default=""/',
            $response->getContent()
        );
        $this->assertStringNotContainsString(
            '<textarea id="create-task-description" name="create_description" rows="6"'."\n".'            placeholder="Describe the task clearly for the team..." class="w-full px-4 py-3"'."\n".'            data-field-default="Should not prefill create modal"',
            $response->getContent()
        );
    }

    public function test_project_task_board_shows_only_fifteen_tasks_before_expanding(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'manager_id' => $user->id,
            'name' => 'Limited Task Board',
            'status' => 'pending',
        ]);

        for ($taskNumber = 1; $taskNumber <= 17; $taskNumber++) {
            Task::create([
                'project_id' => $project->id,
                'title' => "Board Task {$taskNumber}",
                'status' => 'todo',
                'priority' => 'medium',
            ]);
        }

        $response = $this->actingAs($user)->get(route('projects.show', $project));

        $response->assertOk()
            ->assertSee('Voir 2 tâches de plus')
            ->assertSee('data-task-list-toggle', false);

        $this->assertSame(2, substr_count($response->getContent(), 'data-task-overflow'));
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
                'company' => 'company_name',
                'status' => 'pending',
                'column_id' => $column->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'name' => 'Waiting Approval',
            'company' => 'company_name',
            'column_id' => $column->id,
            'manager_id' => $user->id,
        ]);
        $this->assertDatabaseHas('project_columns', [
            'id' => $column->id,
            'user_id' => $user->id,
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
            'user_id' => $user->id,
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

    public function test_custom_board_columns_are_visible_only_to_their_owner(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $column = ProjectColumn::create([
            'user_id' => $owner->id,
            'name' => 'Owner Only',
            'position' => 1,
        ]);
        $project = Project::create([
            'manager_id' => $owner->id,
            'column_id' => $column->id,
            'name' => 'Shared Project',
            'status' => 'pending',
        ]);
        $project->collaborators()->attach($collaborator->id, [
            'invited_by' => $owner->id,
            'accepted_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get('/projects')
            ->assertOk()
            ->assertSee('Owner Only')
            ->assertSee('Shared Project');

        $this->actingAs($collaborator)
            ->get('/projects')
            ->assertOk()
            ->assertDontSee('Owner Only')
            ->assertSee('Shared Project');
    }

    public function test_user_cannot_move_project_to_another_users_custom_column(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = Project::create([
            'manager_id' => $owner->id,
            'name' => 'Protected Project',
            'status' => 'pending',
        ]);
        $otherColumn = ProjectColumn::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Column',
            'position' => 1,
        ]);

        $this->actingAs($owner)
            ->patchJson(route('projects.move', $project), [
                'column_id' => $otherColumn->id,
            ])
            ->assertUnprocessable();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'column_id' => null,
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
