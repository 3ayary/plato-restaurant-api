<?php

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── GET CATEGORIES EDGE CASES ──────────────────────────────────────

describe('get categories edge cases', function () {

    test('returns empty data array when no categories exist', function () {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    });

    test('returns paginated response with correct meta keys', function () {
        // Create more than 10 categories to trigger pagination
        for ($i = 0; $i < 15; $i++) {
            Category::create(['name' => "Category $i"]);
        }

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data'); // paginate(10)
        $response->assertJsonStructure(['data', 'links', 'meta']);
    });

    test('second page returns remaining categories', function () {
        for ($i = 0; $i < 15; $i++) {
            Category::create(['name' => "Category $i"]);
        }

        $response = $this->getJson('/api/categories?page=2');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data'); // 15 - 10 = 5
    });
});

// ─── CREATE CATEGORY EDGE CASES ─────────────────────────────────────

describe('create category edge cases', function () {

    test('non-admin user cannot create category', function () {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->postJson('/api/categories', [
            'name' => 'test',
        ]);

        $response->assertStatus(403);
    });

    test('unauthenticated user cannot create category', function () {
        $response = $this->postJson('/api/categories', [
            'name' => 'test',
        ]);

        $response->assertStatus(401);
    });

    test('create category fails without name', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/categories', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('create category fails with name shorter than 2 chars', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/categories', [
            'name' => 'A',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('create category fails with empty string name', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/categories', [
            'name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });
});

// ─── UPDATE CATEGORY EDGE CASES ─────────────────────────────────────

describe('update category edge cases', function () {

    test('non-admin user cannot update category', function () {
        $user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($user)->putJson("/api/categories/{$category->id}", [
            'name' => 'updated',
        ]);

        $response->assertStatus(403);
    });

    test('unauthenticated user cannot update category', function () {
        $category = Category::create(['name' => 'test']);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'updated',
        ]);

        $response->assertStatus(401);
    });

    test('update category fails with non-existent id', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->putJson('/api/categories/99999', [
            'name' => 'updated',
        ]);

        $response->assertStatus(404);
    });

    test('update category fails without name', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->putJson("/api/categories/{$category->id}", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('update category fails with name shorter than 2 chars', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->putJson("/api/categories/{$category->id}", [
            'name' => 'A',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });
});

// ─── DELETE CATEGORY EDGE CASES ─────────────────────────────────────

describe('delete category edge cases', function () {

    test('non-admin user cannot delete category', function () {
        $user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($user)->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(403);
    });

    test('unauthenticated user cannot delete category', function () {
        $category = Category::create(['name' => 'test']);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(401);
    });

    test('delete category fails with non-existent id', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->deleteJson('/api/categories/99999');

        $response->assertStatus(404);
    });

    test('delete category removes it from database', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $this->actingAs($admin)->deleteJson("/api/categories/{$category->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    });
});
