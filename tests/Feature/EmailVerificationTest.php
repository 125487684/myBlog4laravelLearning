<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function signedUrl(User $user, ?string $hash = null): string
    {
        return URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->getKey(),
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]);
    }

    public function test_registration_sends_verification_email(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'tester',
            'email' => 'tester@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'tester@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_valid_link_verifies_email(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get($this->signedUrl($user))->assertRedirect();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_tampered_id_is_rejected_by_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $url = str_replace('/'.$user->id.'/', '/999/', $this->signedUrl($user));

        $this->actingAs($user)->get($url)->assertForbidden();
    }

    public function test_valid_signature_but_wrong_hash_is_rejected_by_controller(): void
    {
        $user = User::factory()->unverified()->create();

        $url = $this->signedUrl($user, sha1('someone-else@example.com'));

        $this->actingAs($user)->get($url)->assertForbidden();
    }

    public function test_link_without_session_completes_verification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get($this->signedUrl($user))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_link_opened_by_another_logged_in_user_completes_verification(): void
    {
        $owner = User::factory()->unverified()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get($this->signedUrl($owner))
            ->assertRedirect(route('posts.index'))
            ->assertSessionHas('status');

        $this->assertNotNull($owner->fresh()->email_verified_at);
    }

    public function test_unverified_user_connot_create_post(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/posts/create')
            ->assertRedirect(route('verification.notice'));

        $this->actingAs($user)->post('/posts', [
            'title' => 'test title',
            'slug' => 'test-slug',
            'body' => 'test body',
        ])->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_prompt_page_shows_cooldown_right_after_registeration(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'cooler',
            'email' => 'cooler@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->get('/email/verify')
            ->assertSee(__('A fresh verification link has been sent to your email address.'))
            ->assertSee(__('Resend in'));
    }

    public function test_resend_during_cooldown_is_still_allowed(): void
    {
        $user = User::factory()->unverified()->create();

        Notification::fake();

        $this->actingAs($user)->post('/email/verification-notification');

        Notification::assertSentTo($user, VerifyEmail::class);

        $this->actingAs($user)->post('/email/verification-notification')
            ->assertRedirect();

        Notification::assertSentTo($user, VerifyEmail::class, 2);
    }

    public function test_resend_has_a_hard_limit_of_six_per_minute(): void
    {
        $user = User::factory()->unverified()->create();

        foreach (range(1, 6) as $i) {
            $this->actingAs($user)->post('/email/verification-notification');
        }

        $this->actingAs($user)->post('/email/verification-notification')
            ->assertStatus(429);
    }

    public function test_status_endpoint_returns_json_for_api_clients(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->getJson(route('verification.status'))
            ->assertOk()
            ->assertJson(['verified' => false]);

        $user->markEmailAsVerified();

        $this->actingAs($user)
            ->getJson(route('verification.status'))
            ->assertOk()
            ->assertJson(['verified' => true]);
    }
}
