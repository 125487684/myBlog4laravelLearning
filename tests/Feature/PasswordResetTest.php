<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private function requestResetLink(User $user): string
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => $user->email]);

        return Notification::sent($user, ResetPassword::class)->first()->token;
    }

    public function test_reset_link_is_sent_for_know_email(): void
    {
        $user = User::factory()->create();

        Notification::fake();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_unknown_email_is_rejected(): void
    {
        $this->post('/forgot-password', ['email' => 'ghost@example.com'])
            ->assertSessionHasErrors('email');
    }

    public function test_full_reset_flow_changes_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $token = $this->requestResetLink($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_same_as_old_password_is_rejected_and_token_survives(): void
    {
        $user = User::factory()->create(['password' => 'same-password']);
        $token = $this->requestResetLink($user);

        $this->post('reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'same-password',
            'password_confirmation' => 'same-password',
        ]);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->post('/reset-password', [
            'token' => 'garbage-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_resend_is_throttled(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);
        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHasErrors('email');
    }
}
