<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $manager = User::where('email', 'test@example.com')->first();

        $projects = [
            [
                'name' => 'Ways to get a slipping project back on track',
                'description' => 'Audit the current workflow, identify blockers, and prepare a short recovery plan for the content team.',
                'status' => 'pending',
                'assigned_to' => 'Alex Martin',
                'start_date' => now()->subDays(2)->toDateString(),
                'end_date' => now()->addDays(6)->toDateString(),
            ],
            [
                'name' => 'Top Asana alternatives',
                'description' => 'Compare project management tools for the PH Marketing workspace and prepare recommendation notes.',
                'status' => 'pending',
                'assigned_to' => 'Sarah Kim',
                'start_date' => now()->subDay()->toDateString(),
                'end_date' => now()->addDays(8)->toDateString(),
            ],
            [
                'name' => 'Tools for entrepreneurs',
                'description' => 'Create a research list of the best SaaS tools for founders and early-stage marketing teams.',
                'status' => 'pending',
                'assigned_to' => 'Lina Noor',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
            ],
            [
                'name' => 'How to automate repetitive tasks',
                'description' => 'Document simple automation wins using forms, templates, and notification workflows.',
                'status' => 'in_progress',
                'assigned_to' => 'Youssef Amrani',
                'start_date' => now()->subDays(5)->toDateString(),
                'end_date' => now()->addDays(3)->toDateString(),
            ],
            [
                'name' => 'Project manager daily tasks',
                'description' => 'Map the daily recurring checklist used to monitor progress, blockers, and deadlines.',
                'status' => 'in_progress',
                'assigned_to' => 'Nora Fadel',
                'start_date' => now()->subDays(7)->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
            ],
            [
                'name' => '10 productivity hacks for remote teams',
                'description' => 'Build a practical article draft with examples for async work, focus blocks, and reporting habits.',
                'status' => 'in_progress',
                'assigned_to' => 'Omar Idrissi',
                'start_date' => now()->subDays(3)->toDateString(),
                'end_date' => now()->addDays(4)->toDateString(),
            ],
            [
                'name' => 'How better to handle deadlines as a team',
                'description' => 'Summarize deadline rituals and propose a review process before delivery.',
                'status' => 'completed',
                'assigned_to' => 'Maya Chen',
                'start_date' => now()->subDays(12)->toDateString(),
                'end_date' => now()->subDays(2)->toDateString(),
            ],
            [
                'name' => 'Making mistakes as a manager',
                'description' => 'Finalize the article structure and publish the reviewed content in the internal knowledge base.',
                'status' => 'completed',
                'assigned_to' => 'Samir B.',
                'start_date' => now()->subDays(9)->toDateString(),
                'end_date' => now()->subDays(1)->toDateString(),
            ],
            [
                'name' => 'Weekly content review workflow',
                'description' => 'Close the loop on editorial review, comments, and approval handoff between team members.',
                'status' => 'completed',
                'assigned_to' => 'Jade Pierre',
                'start_date' => now()->subDays(15)->toDateString(),
                'end_date' => now()->subDays(4)->toDateString(),
            ],
        ];

        foreach ($projects as $project) {
            Project::updateOrCreate(
                ['name' => $project['name']],
                array_merge($project, ['manager_id' => $manager?->id]),
            );
        }
    }
}
