<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('category', function () {

    test('get All categories', function () {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    });

    test('create category', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/categories',
            [
                'name' => 'test',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);

    });

    test('update category', function () {

        $admin = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['name' => 'test']);
        $response = $this->actingAs($admin)->putJson("/api/categories/{$category->id}", ['name' => 'updated Test']);

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    });

    test('delete category', function () {

        $admin = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['name' => 'test']);
        $response = $this->actingAs($admin)->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);

    });

});
