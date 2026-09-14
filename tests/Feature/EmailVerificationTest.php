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

    public function test_guest_is_redirected_to_login(): void
    {
        $user = User::factory()->unverified()->create();

        $this->get($this->signedUrl($user))->assertRedirect('/login');
    }

    public function test_valid_link_of_anothor_user_is_rejected(): void
    {
        $owner = User::factory()->unverified()->create();
        $other = User::factory()->create();

        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $owner->getKey(),
            'hash' => sha1($owner->getEmailForVerification()),
        ]);

        $this->actingAs($other)->get($url)
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('error');

        $this->assertNull($owner->fresh()->email_verified_at);
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
}
