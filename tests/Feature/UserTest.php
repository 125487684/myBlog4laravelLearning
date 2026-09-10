<?php

namespace Tests\Feature;

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
}
