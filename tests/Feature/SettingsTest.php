<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class settingsTest extends TestCase
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
}
