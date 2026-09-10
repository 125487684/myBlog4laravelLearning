<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_register_name_longer_than20(): void
    {
        $this->post('/register', [
            'name' => str_repeat('a', 21),
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertInvalid('name');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_login_is_throttled_after_five_failure(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $i) {
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect()->assertSessionHasErrors('email');
    }
}
