<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'P@ssw0rd123',
            'password_confirmation' => 'P@ssw0rd123',
        ]);

        $response->assertStatus(204)
            ->assertNoContent();
    }
}
