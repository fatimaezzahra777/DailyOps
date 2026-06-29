<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_open_temporary_support_chat_when_email_matches_project(): void
    {
        $manager = User::factory()->create(['role' => 'member']);

        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Site vitrine',
            'status' => 'in_progress',
            'client_email' => 'client@example.com',
        ]);

        $response = $this->post(route('support.store'), [
            'first_name' => 'Sara',
            'last_name' => 'Client',
            'email' => ' CLIENT@example.com ',
            'phone' => '0612345678',
            'title' => 'Probleme de connexion',
            'description' => 'Je ne peux pas acceder a mon espace.',
        ]);

        $conversation = SupportConversation::first();

        $response->assertRedirect(route('support.chat.show', $conversation->token));

        $this->assertSame($project->id, $conversation->project_id);
        $this->assertSame($manager->id, $conversation->manager_id);
        $this->assertSame('client@example.com', $conversation->email);
        $this->assertTrue($conversation->expires_at->between(now()->addHours(47)->addMinutes(59), now()->addHours(48)->addMinute()));

        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'sender_type' => SupportMessage::SENDER_CLIENT,
            'sender_name' => 'Sara Client',
            'body' => 'Je ne peux pas acceder a mon espace.',
        ]);
    }

    public function test_client_email_must_match_existing_project_client_email(): void
    {
        $this->post(route('support.store'), [
            'first_name' => 'Sara',
            'last_name' => 'Client',
            'email' => 'unknown@example.com',
            'phone' => '0612345678',
            'title' => 'Question',
            'description' => 'Bonjour.',
        ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('support_conversations', 0);
    }

    public function test_project_manager_can_reply_to_support_conversation(): void
    {
        $manager = User::factory()->create(['role' => 'member', 'name' => 'Manager One']);
        $project = Project::create([
            'manager_id' => $manager->id,
            'name' => 'Application mobile',
            'status' => 'pending',
            'client_email' => 'client@example.com',
        ]);
        $conversation = SupportConversation::create([
            'project_id' => $project->id,
            'manager_id' => $manager->id,
            'token' => 'support-token',
            'first_name' => 'Sara',
            'last_name' => 'Client',
            'email' => 'client@example.com',
            'title' => 'Bug',
            'description' => 'Details',
            'expires_at' => now()->addHours(48),
        ]);

        $this->actingAs($manager)
            ->post(route('support.manager.messages.store', $conversation), [
                'body' => 'Je regarde le probleme.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('support_messages', [
            'support_conversation_id' => $conversation->id,
            'user_id' => $manager->id,
            'sender_type' => SupportMessage::SENDER_MANAGER,
            'sender_name' => 'Manager One',
            'body' => 'Je regarde le probleme.',
        ]);
    }
}
