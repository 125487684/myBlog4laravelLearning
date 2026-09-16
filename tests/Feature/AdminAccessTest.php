<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        config(['admin.email' => 'admin@example.com', 'admin.password' => 'secret-pass']);

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue((bool) $admin->is_admin);
        $this->assertTrue(Hash::check('secret-pass', $admin->password));   // 顺手锁 hashed cast
    }

    public function test_seeder_skips_admin_without_credentials(): void
    {
        config(['admin.email' => null, 'admin.password' => null]);

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(0, User::where('is_admin', true)->count());
    }

    public function test_seeder_is_idempotent_for_existing_admin(): void
    {
        config(['admin.email' => 'admin@example.com', 'admin.password' => 'first-pass']);

        $existing = User::factory()->create([
            'email' => 'admin@example.com',
        ]);
        $existing->forceFill(['password' => 'manully-changed'])->save();

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@example.com')->first();

        $this->assertSame($existing->id, $admin->id);
        $this->assertTrue(Hash::check('manully-changed', $admin->password));
        $this->assertTrue((bool) $admin->is_admin);
    }
}
