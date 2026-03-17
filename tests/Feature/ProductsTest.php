<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

describe('products', function () {

    test('get products', function () {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
    });

    test('create product', function () {

        $mockResult = Mockery::mock();
        $mockResult->shouldReceive('offsetGet')->with('secure_url')->andReturn('https://fake.com/image.jpg');

        $mockApi = Mockery::mock();
        $mockApi->shouldReceive('upload')->andReturn($mockResult);

        $mockCloudinary = Mockery::mock();
        $mockCloudinary->shouldReceive('uploadApi')->andReturn($mockApi);

        $this->app->instance('cloudinary', $mockCloudinary);

        $category = Category::create(['name' => 'test']);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/products', [
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
            'description' => 'test',
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'data']);
    });

    test('update product', function () {

        $category = Category::create(['name' => 'test']);
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
            'description' => 'test',
            'image' => 'https://fake.com/old-image.jpg',
        ]);

        $response = $this->actingAs($admin)->putJson("/api/products/{$product->id}", [
            'name' => 'test updated',
            'price' => 200,
            'category_id' => $category->id,
            'description' => 'test updated',
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data']);
    });

    test('delete product', function () {
        $category = Category::create(['name' => 'test']);
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
            'description' => 'test',
            'image' => UploadedFile::fake()->image('product.jpg'),
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'data']);
    });

});
