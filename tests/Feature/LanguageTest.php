<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_guest_switch_to_chinese(): void
    {
        $this->get('/language/zh')->assertRedirect()->assertCookie('locale', 'zh');

        $this->withCookie('locale', 'zh')->get('/posts')->assertSee('登录');
    }

    public function test_guest_can_switch_back_to_english(): void
    {
        $this->get('/language/zh')->assertRedirect();
        $this->get('/language/en')->assertRedirect()->assertCookie('locale', 'en');

        $this->withCookie('locale', 'en')->get('/posts')->assertSee('Log in');
    }

    public function test_cookie_locale_is_saved_to_user_on_login(): void
    {
        $user = User::factory()->create();

        $this->get('/language/zh')->assertRedirect()->assertCookie('locale', 'zh');
        $this->withCookie('locale', 'zh')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/posts');

        $this->get('/posts')->assertOk();
        $this->assertSame('zh', $user->fresh()->locale);
    }

    public function test_database_locale_wins_over_cookie(): void
    {
        $user = User::factory()->create(['locale' => 'zh']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('/posts');

        $this->withCookie('locale', 'en')->get('/posts')->assertSee('登出');
        $this->assertSame('zh', $user->fresh()->locale);
    }

    public function test_authenticated_user_switch_upates_database(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/language/zh')->assertRedirect();

        $this->assertSame('zh', $user->fresh()->locale);
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_locale_returns400(): void
    {
        $this->get('/language/fr')->assertStatus(400);
    }
}
