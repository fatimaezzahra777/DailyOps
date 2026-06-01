<?php

namespace Tests\Feature;

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
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'member-ready@example.com',
            'role' => 'member',
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
}
