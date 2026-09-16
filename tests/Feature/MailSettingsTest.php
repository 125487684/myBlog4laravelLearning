<?php

namespace Tests\Feature;

use App\Models\MailSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_modify_mail_server(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put('/settings/mail', [
            'host' => 'right.example.com',
            'port' => 1025,
            'encryption' => '',
            'username' => 'admin',
            'from_address' => 'right@example.com',
            'from_name' => 'right',
        ])->assertRedirect(route('mail-settings.edit'));

        $this->assertDatabaseHas('mail_settings', ['host' => 'right.example.com']);
    }

    public function test_password_is_kept_when_left_empty(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put('/settings/mail', [
            'host' => 'smtp.example.com',
            'port' => 465,
            'encrption' => 'ssl',
            'username' => 'mailer@example.com',
            'password' => 'secret-auth-code',
            'from_address' => 'mailer@example.com',
            'from_name' => 'Blog',
        ]);

        $this->actingAs($admin)->put('/settings/mail', [
            'host' => 'changed.example.com',
            'port' => 465,
            'encryption' => 'ssl',
            'username' => 'mailer@example.com',
            'password' => '',
            'from_address' => 'mailer@example.com',
            'from_name' => 'Blog',
        ])->assertRedirect(route('mail-settings.edit'));

        // 断言：host 变了，密码原样（模型层拿到的是解密后的明文）
        $this->assertSame('changed.example.com', MailSetting::first()->host);
        $this->assertSame('secret-auth-code', MailSetting::first()->password);
    }

    public function test_password_is_stored_encrypted(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put('/settings/mail', [
            'host' => 'smtp.example.com',
            'port' => 465,
            'encrption' => 'ssl',
            'username' => 'mailer@example.com',
            'password' => 'secret-auth-code',
            'from_address' => 'mailer@example.com',
            'from_name' => 'Blog',
        ]);

        // 模型层原文，数据库密文
        $this->assertSame('secret-auth-code', MailSetting::first()->password);
        $this->assertNotSame('secret-auth-code', DB::table('mail_settings')->value('password'));
    }

    public function test_validation_rejects_invalid_port(): void
    {
        $admin = User::factory()->admin()->create();

        // 触发 current() 初始化
        // 测试进程的应用生命周期与 HTTP 请求不同
        $this->actingAs($admin)->get('/settings/mail')->assertOk();

        $this->actingAs($admin)->put('/settings/mail', [
            'host' => 'smtp.example.com',
            'port' => 70000,
            'encryption' => 'ssl',
            'username' => 'mailer@example.com',
            'from_address' => 'mailer@example.com',
            'from_name' => 'Blog',
        ])->assertSessionHasErrors('port');

        $this->assertDatabaseHas('mail_settings', ['host' => 'localhost']);
        $this->assertSame(1, MailSetting::count());
    }
}
