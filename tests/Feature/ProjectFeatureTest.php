<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_index_keeps_filtered_counts_across_pagination(): void
    {
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

    public function test_database_seeder_can_run_twice_without_duplicate_test_user(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertDatabaseCount('projects', 9);
    }
}
