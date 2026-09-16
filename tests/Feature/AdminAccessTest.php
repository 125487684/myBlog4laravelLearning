<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/settings/mail')
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_view_mail_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/settings/mail')
            ->assertStatus(403);
    }

    public function test_non_admin_cannot_update_mail_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings/mail', [
            'host' => 'evil.example.com',
            'post' => 25,
            'encryption' => '',
            'username' => '',
            'from_address' => 'evil.example.com',
            'from_name' => 'Evil',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('mail_settings', ['host' => 'evil.example.com']);
    }

    public function test_admin_can_view_mail_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/settings/mail')
            ->assertStatus(200)
            ->assertSee('SMTP host');
    }

    public function test_seeder_creates_admin_users(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue((bool) $admin->is_admin);
    }
}
