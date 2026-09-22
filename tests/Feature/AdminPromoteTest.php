<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPromoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_upgrade_to_admin(): void
    {
        $user = User::factory()->create();

        $this->artisan('admin:promote', ['email' => $user->email, '--force' => true])
            ->expectsOutputToContain('has been promoted to admin.')
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_unknown_email_fails(): void
    {
        $this->artisan('admin:promote', ['email' => 'ghost@example.com'])
            ->expectsOutputToContain('No user found for email')
            ->assertFailed();
    }

    public function test_already_admin_is_idempotent(): void
    {
        $user = User::factory()->admin()->create();

        $this->artisan('admin:promote', ['email' => $user->email, '--force' => true])
            ->expectsOutputToContain('is already an admin, nothing to do.')
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_promotion_succeeds_with_interactive_confirmation(): void
    {
        $user = User::factory()->create();

        $this->artisan('admin:promote', ['email' => $user->email])
            ->expectsConfirmation("Promote {$user->name} ({$user->email}) to administrator?", 'yes')
            ->expectsOutputToContain('has been promoted to admin.')
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }

    public function test_promotion_is_declined_without_confirmation(): void
    {
        $user = User::factory()->create();

        $this->artisan('admin:promote', ['email' => $user->email])
            ->expectsConfirmation("Promote {$user->name} ({$user->email}) to administrator?", false)
            ->expectsOutputToContain('Aborted')
            ->assertFailed();

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_ambiguous_case_variant_emails_are_refused(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);
        User::factory()->create(['email' => 'ALICE@example.com']);

        $this->artisan('admin:promote', ['email' => 'Alice@example.com'])
            ->expectsOutputToContain('Multiple accounts match')
            ->assertFailed();
    }

    public function test_unverified_email_is_refused(): void
    {
        $user = User::factory()->unverified()->create();

        $this->artisan('admin:promote', ['email' => $user->email])
            ->expectsOutputToContain('has not verified their email')
            ->assertFailed();

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_email_lookup_is_case_insensitive(): void
    {
        $user = User::factory()->create();

        $this->artisan('admin:promote', ['email' => strtoupper($user->email), '--force' => true])
            ->expectsOutputToContain('has been promoted to admin.')
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->is_admin);
    }
}
