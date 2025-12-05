<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Create an admin user.
     */
    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->admin()->create($attributes);
    }

    /**
     * Create a regular user.
     */
    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    /**
     * Create a guest user.
     */
    protected function createGuest(array $attributes = []): User
    {
        return User::factory()->guest()->create($attributes);
    }

    /**
     * Assert that a user is authenticated.
     */
    public function assertAuthenticated($guard = null)
    {
        $this->assertTrue(auth($guard)->check(), 'The user is not authenticated.');
        return $this;
    }

    /**
     * Assert that a user is not authenticated.
     */
    public function assertGuest($guard = null)
    {
        $this->assertFalse(auth($guard)->check(), 'The user is authenticated.');
        return $this;
    }
}
