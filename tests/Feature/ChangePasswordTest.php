<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_to_change_password_and_keep_login(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_reject_wrong_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertInvalid('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    public function test_reject_same_password(): void
    {
        $user = User::factory()->create(['password' => 'same-passwrod']);

        $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'same-password',
            'password' => 'same-passwrod',
            'password_confirmation' => 'same-password',
        ])->assertInvalid('password');
    }

    public function test_reject_different_password_confirmation(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ])->assertInvalid('password');
    }

    public function test_password_too_short(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);

        $this->actingAs($user)->put('/settings/password', [
            'current_password' => 'old-password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertInvalid('password');
    }

    public function test_guest_back_to_login(): void
    {
        $this->put('/settings/password', [
            'current_password' => 'x',
            'password' => 'x',
            'password_confirmation' => 'x',
        ])->assertRedirect('/login');
    }
}
