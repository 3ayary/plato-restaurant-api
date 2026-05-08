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
        $response->assertJsonStructure(['data']);
    });

});
