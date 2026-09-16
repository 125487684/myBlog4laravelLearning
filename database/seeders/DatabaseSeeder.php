<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();
        Post::factory()->count(20)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 管理员只在显式提供凭据的环境创建；生产默认不建，走"注册后提权"流程
        // 幂等：邮箱已存在时不重建
        // ‘0’ 是合法但为假的密码字符串
        $adminEmail = (string) config('admin.email');
        $adminPassword = (string) config('admin.password');

        if ($adminEmail !== '' && $adminPassword !== '') {
            $admin = User::firstOrCreate(
                ['email' => $adminEmail],
                ['name' => 'admin', 'password' => $adminPassword],
            );

            if (! $admin->is_admin) {
                $admin->forceFill(['is_admin' => true])->save();
            }
        }
    }
}
