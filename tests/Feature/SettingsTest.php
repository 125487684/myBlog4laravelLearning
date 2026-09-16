<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
