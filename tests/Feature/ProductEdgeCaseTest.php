<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

// ─── HELPER: setup Cloudinary mock ──────────────────────────────────

function mockCloudinary($testInstance): void
{
    $mockResult = Mockery::mock();
    $mockResult->shouldReceive('offsetGet')->with('secure_url')->andReturn('https://fake.com/image.jpg');

    $mockApi = Mockery::mock();
    $mockApi->shouldReceive('upload')->andReturn($mockResult);
    $mockApi->shouldReceive('destroy')->andReturn(true);

    $mockCloudinary = Mockery::mock();
    $mockCloudinary->shouldReceive('uploadApi')->andReturn($mockApi);

    $testInstance->app->instance('cloudinary', $mockCloudinary);
}

// ─── GET PRODUCTS EDGE CASES ────────────────────────────────────────

describe('get products edge cases', function () {

    test('returns empty data when no products exist', function () {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    });

    test('returns paginated response (5 per page)', function () {
        $category = Category::create(['name' => 'test']);
        for ($i = 0; $i < 8; $i++) {
            Product::create([
                'name' => "Product $i",
                'price' => 10 + $i,
                'category_id' => $category->id,
            ]);
        }

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    });

    test('search filter returns matching products', function () {
        $category = Category::create(['name' => 'test']);
        Product::create(['name' => 'Pizza Margherita', 'price' => 10, 'category_id' => $category->id]);
        Product::create(['name' => 'Pasta Carbonara', 'price' => 12, 'category_id' => $category->id]);
        Product::create(['name' => 'Pizza Pepperoni', 'price' => 14, 'category_id' => $category->id]);

        $response = $this->getJson('/api/products?search=Pizza');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    });

    test('search filter returns empty when no match', function () {
        $category = Category::create(['name' => 'test']);
        Product::create(['name' => 'Pizza', 'price' => 10, 'category_id' => $category->id]);

        $response = $this->getJson('/api/products?search=NonExistentProduct');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    });

    test('category_id filter returns products from specific category', function () {
        $category1 = Category::create(['name' => 'Pizzas']);
        $category2 = Category::create(['name' => 'Pastas']);
        Product::create(['name' => 'Pizza', 'price' => 10, 'category_id' => $category1->id]);
        Product::create(['name' => 'Pasta', 'price' => 12, 'category_id' => $category2->id]);

        $response = $this->getJson("/api/products?category_id={$category1->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    test('combined search and category_id filter works', function () {
        $category1 = Category::create(['name' => 'Pizzas']);
        $category2 = Category::create(['name' => 'Pastas']);
        Product::create(['name' => 'Spicy Pizza', 'price' => 10, 'category_id' => $category1->id]);
        Product::create(['name' => 'Spicy Pasta', 'price' => 12, 'category_id' => $category2->id]);
        Product::create(['name' => 'Plain Pizza', 'price' => 8, 'category_id' => $category1->id]);

        $response = $this->getJson("/api/products?search=Spicy&category_id={$category1->id}");

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });
});

// ─── CREATE PRODUCT EDGE CASES ──────────────────────────────────────

describe('create product edge cases', function () {

    test('unauthenticated user cannot create product', function () {
        $response = $this->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
            'category_id' => 1,
        ]);

        $response->assertStatus(401);
    });

    test('non-admin user cannot create product', function () {
        $user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($user)->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(403);
    });

    test('create product fails without name', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'price' => 100,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('create product fails without price', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['price']);
    });

    test('create product fails without category_id', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
    });

    test('create product fails with non-existent category_id', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
            'category_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
    });

    test('create product fails with non-numeric price', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'price' => 'not-a-number',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['price']);
    });

    test('create product fails with name shorter than 2 chars', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'A',
            'price' => 100,
            'category_id' => $category->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    });

    test('create product fails with empty body', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/products', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'price', 'category_id']);
    });

    test('create product succeeds without image (image is nullable)', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
            'description' => 'test description',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);
    });

    test('create product fails with non-image file', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
            'image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    });

    test('create product fails with image exceeding 2MB', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
            'image' => UploadedFile::fake()->image('large.jpg')->size(3000), // 3MB
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['image']);
    });
});

// ─── UPDATE PRODUCT EDGE CASES ──────────────────────────────────────

describe('update product edge cases', function () {

    test('unauthenticated user cannot update product', function () {
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'updated',
        ]);

        $response->assertStatus(401);
    });

    test('non-admin user cannot update product', function () {
        $user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->putJson("/api/products/{$product->id}", [
            'name' => 'updated',
        ]);

        $response->assertStatus(403);
    });

    test('update product fails with non-existent id', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->putJson('/api/products/99999', [
            'name' => 'updated',
        ]);

        $response->assertStatus(404);
    });

    test('update product fails with non-existent category_id', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->putJson("/api/products/{$product->id}", [
            'category_id' => 99999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
    });

    test('update product fails with non-numeric price', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->putJson("/api/products/{$product->id}", [
            'price' => 'not-a-number',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['price']);
    });

    test('update product allows partial update (only name)', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'original', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->putJson("/api/products/{$product->id}", [
            'name' => 'updated',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'updated',
            'price' => 100, // unchanged
        ]);
    });
});

// ─── DELETE PRODUCT EDGE CASES ──────────────────────────────────────

describe('delete product edge cases', function () {

    test('unauthenticated user cannot delete product', function () {
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(401);
    });

    test('non-admin user cannot delete product', function () {
        $user = User::factory()->create(['role' => 'customer']);
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(403);
    });

    test('delete product fails with non-existent id', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->deleteJson('/api/products/99999');

        $response->assertStatus(404);
    });

    test('delete product removes it from database', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test', 'price' => 100,
            'category_id' => $category->id,
        ]);

        $this->actingAs($admin)->deleteJson("/api/products/{$product->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    });
});
