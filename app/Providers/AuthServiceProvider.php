<?php

namespace App\Providers;

use App\Models\MailSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('admin', function (User $user) {
            return (bool) $user->is_admin;
        });

        try {
            $mail = MailSetting::current();
        } catch (\Throwable) {
            return; // 表还没建时沿用 .env 的配置
        }

        Config::set([
            'mail.mailers.smtp.host' => $mail->host,
            'mail.mailers.smtp.port' => $mail->port,
            'mail.mailers.smtp.encryption' => $mail->encryption ?: null,
            'mail.mailers.smtp.username' => $mail->username,
            'mail.mailers.smtp.password' => $mail->password,
            'mail.from.address' => $mail->from_address,
            'mail.from.name' => $mail->from_name,
        ]);
    }
}
