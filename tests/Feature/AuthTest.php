<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('login', function () {

    beforeEach(function () {
        $this->user = User::Create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '01012345678',
            'password' => 'test1234',
        ]);
    });

    test('login successfully', function () {
        $response = $this->postJson('api/login', [
            'email' => 'test@test.com',
            'password' => 'test1234',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['user', 'token']);
    });

});

describe('logout', function () {

    test('logout successfully', function () {

        $user = User::factory()->create();

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('authorization', 'Bearer '.$token)->postJson('/api/logout');

        $response->assertStatus(200);

    });
});

describe('register', function () {

    test('register successfully', function () {
        $response = $this->postJson('api/register',
            [
                'name' => 'Test User',
                'email' => 'test@test.com',
                'phone' => '01012345678',
                'password' => 'test1234',
                'password_confirmation' => 'test1234',

            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['user', 'token']);
    });
});
