<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/settings')->assertRedirect('/login');
    }

    public function test_name_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings', ['name' => 'new name'])->assertRedirect('/settings');

        $this->assertSame('new name', $user->fresh()->name);
    }

    public function test_empty_name_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings', ['name' => ''])->assertInvalid('name');
    }

    public function test_admin_sees_both_tabs(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/settings')
            ->assertOk()
            ->assertSee(__('Profile settings'))
            ->assertSee(__('Admin settings'));
    }

    public function test_normal_users_see_one_tab(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/settings')
            ->assertOk()
            ->assertSee(__('Profile settings'))
            ->assertDontSee(__('Admin settings'));
    }

    public function test_non_admin_requesting_admin_tab_falls_back_to_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/settings?tab=admin')
            ->assertOk()
            ->assertSee(__('Profile settings'))
            ->assertDontSee(__('Admin settings'));
    }

    public function test_user_can_change_email_with_correct_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();   // 默认已验证

        $this->actingAs($user)->put('/settings/email', [
            'email' => 'new@example.com',
            'current_password' => 'password',   // 工厂默认密码
        ])->assertRedirect(route('verification.notice'));

        // 邮箱变了 + 降级为未验证（核心业务语义）
        $this->assertSame('new@example.com', $user->fresh()->email);
        $this->assertNull($user->fresh()->email_verified_at);

        // 新邮箱收到验证邮件
        Notification::assertSentTo($user->fresh(), VerifyEmail::class);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings/email', [
            'email' => 'new@example.com',
            'current_password' => 'wrong-password',
        ])->assertSessionHasErrors('current_password', null, 'email');

        $this->assertSame($user->email, $user->fresh()->email);   // 邮箱未变
    }

    public function test_duplicate_email_is_rejected(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings/email', [
            'email' => 'taken@example.com',
            'current_password' => 'password',
        ])->assertSessionHasErrors('email', null, 'email');
    }

    public function test_invalid_email_format_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/settings/email', [
            'email' => 'not-an-email',
            'current_password' => 'password',
        ])->assertSessionHasErrors('email', null, 'email');
    }

    public function test_same_email_is_rejected(): void
    {
        $user = User::factory()->create();   // 默认已验证

        $this->actingAs($user)->put('/settings/email', [
            'email' => $user->email,           // 与当前相同
            'current_password' => 'password',
        ])->assertSessionHasErrors('email', null, 'email');   // 注意指定 email 袋

        // 关键：未降级
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
