<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── REGISTER EDGE CASES ────────────────────────────────────────────

describe('register edge cases', function () {

    test('register fails without name', function () {
        $response = $this->postJson('api/register', [
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('register fails without email', function () {
        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'phone' => '01012345678',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('register fails without phone', function () {
        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    });

    test('register fails without password', function () {
        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '01012345678',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('register fails with empty body', function () {
        $response = $this->postJson('api/register', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'phone', 'password']);
    });

    test('register fails with invalid email format', function () {
        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'phone' => '01012345678',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('register fails with duplicate email', function () {
        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'email' => 'existing@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('register fails with short password (less than 8 chars)', function () {
        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('register fails when password confirmation does not match', function () {
        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
            'password_confirmation' => 'different',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('register fails without password confirmation', function () {
        $response = $this->postJson('api/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('register fails with name shorter than 2 characters', function () {
        $response = $this->postJson('api/register', [
            'name' => 'A',
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('register fails with non-string name (integer)', function () {
        $response = $this->postJson('api/register', [
            'name' => 12345,
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
            'password_confirmation' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });
});

// ─── LOGIN EDGE CASES ───────────────────────────────────────────────

describe('login edge cases', function () {

    beforeEach(function () {
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
        ]);
    });

    test('login fails with wrong password', function () {
        $response = $this->postJson('api/login', [
            'email' => 'test@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'invalid credentials']);
    });

    test('login fails with non-existent email', function () {
        $response = $this->postJson('api/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'test1234',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'invalid credentials']);
    });

    test('login fails without email', function () {
        $response = $this->postJson('api/login', [
            'password' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('login fails without password', function () {
        $response = $this->postJson('api/login', [
            'email' => 'test@test.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });

    test('login fails with empty body', function () {
        $response = $this->postJson('api/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    });

    test('login fails with invalid email format', function () {
        $response = $this->postJson('api/login', [
            'email' => 'not-an-email',
            'password' => 'test1234',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    });

    test('login fails with short password (less than 8 chars)', function () {
        $response = $this->postJson('api/login', [
            'email' => 'test@test.com',
            'password' => 'short',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    });
});

// ─── LOGOUT EDGE CASES ──────────────────────────────────────────────

describe('logout edge cases', function () {

    test('logout fails without authentication token', function () {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    });

    test('logout fails with invalid bearer token', function () {
        $response = $this->withHeader('authorization', 'Bearer invalid-token-here')
            ->postJson('/api/logout');

        $response->assertStatus(401);
    });

    test('logout fails with revoked token (double logout)', function () {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        // First logout - should succeed
        $this->withHeader('authorization', 'Bearer '.$token)
            ->postJson('/api/logout')
            ->assertStatus(200);

        // Reset auth guard so the cached user is cleared (simulates separate HTTP request)
        auth()->forgetGuards();

        // Second logout with same token - should fail
        $response = $this->withHeader('authorization', 'Bearer '.$token)
            ->postJson('/api/logout');

        $response->assertStatus(401);
    });
});
