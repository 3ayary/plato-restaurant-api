<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('orders', function () {

    test('create order', function () {

        $user = User::factory()->create();

        $category = Category::create(['name' => 'test']);
        $product = Product::create([
            'name' => 'test',
            'price' => 100,
            'category_id' => $category->id,
            'description' => 'test',
            'image' => 'test',
        ]);
        $response = $this->actingAs($user)->postJson('/api/orders',
            [
                'address' => 'test address',
                'phone' => '01012345678',
                'notes' => 'test',
                'user_id' => $user->id,
                'total_price' => 0,
                'status' => 'pending',
                'items' => [
                    ['product_id' => $product->id, 'quantity' => 3],
                ],
            ]);

        $response->assertStatus(201);
    });

    test('get all user orders', function () {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->getJson('/api/orders');

        $response->assertStatus(200);

    });

    test('get one order', function () {

        $user = User::factory()->create();
        $order = Order::create(
            [
                'address' => 'test address',
                'phone' => '01012345678',
                'notes' => 'test',
                'user_id' => $user->id,
                'total_price' => 0,
                'status' => 'pending',

            ]);

        $response = $this->actingAs($user)->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);

    });

    test('update order status', function () {
        $user = User::factory()->create();
        $order = Order::create(
            [
                'address' => 'test address',
                'phone' => '01012345678',
                'notes' => 'test',
                'user_id' => $user->id,
                'total_price' => 0,
                'status' => 'pending',

            ]);

        $response = $this->actingAs($user)->putJson("/api/orders/{$order->id}",
            [
                'status' => 'delivered',
            ]);

        $response->assertStatus(200);

    });

});
