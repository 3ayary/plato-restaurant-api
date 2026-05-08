<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── CREATE ORDER EDGE CASES ────────────────────────────────────────

describe('create order edge cases', function () {

    test('unauthenticated user cannot create order', function () {
        $response = $this->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ]);

        $response->assertStatus(401);
    });

    test('create order fails without address', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'phone' => '01012345678',
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
    });

    test('create order fails without phone', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'items' => [['product_id' => 1, 'quantity' => 1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    });

    test('create order fails without items', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    });

    test('create order fails with empty items array', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    });

    test('create order fails with empty body', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address', 'phone', 'items']);
    });

    test('create order fails with short address (less than 8 chars)', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'short',
            'phone' => '01012345678',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);
    });

    test('create order fails with short phone (less than 9 chars)', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address here',
            'phone' => '1234',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    });

    test('create order fails with non-existent product_id', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => 99999, 'quantity' => 1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.product_id']);
    });

    test('create order fails with zero quantity', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => $product->id, 'quantity' => 0]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.quantity']);
    });

    test('create order fails with negative quantity', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => $product->id, 'quantity' => -5]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.quantity']);
    });

    test('create order fails with missing product_id in item', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['quantity' => 1]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.product_id']);
    });

    test('create order fails with missing quantity in item', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => $product->id]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.quantity']);
    });

    test('create order calculates total price correctly with multiple items', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product1 = Product::create(['name' => 'Product 1', 'price' => 50, 'category_id' => $category->id]);
        $product2 = Product::create(['name' => 'Product 2', 'price' => 30, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2], // 50 * 2 = 100
                ['product_id' => $product2->id, 'quantity' => 3], // 30 * 3 = 90
            ],
        ]);

        $response->assertStatus(201);

        // total_price should be 190
        $order = Order::first();
        expect((float) $order->total_price)->toBe(190.00);
    });

    test('create order sets status to pending', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['status' => 'pending']);
    });

    test('create order with notes (nullable field)', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'notes' => 'Extra sauce please',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['notes' => 'Extra sauce please']);
    });

    test('create order without notes succeeds', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $response->assertStatus(201);
    });

    test('create order with non-integer quantity fails', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => [['product_id' => $product->id, 'quantity' => 1.5]],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.quantity']);
    });

    test('create order with items as non-array fails', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'address' => 'test address',
            'phone' => '01012345678',
            'items' => 'not-an-array',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items']);
    });
});

// ─── GET ORDERS EDGE CASES ──────────────────────────────────────────

describe('get orders edge cases', function () {

    test('unauthenticated user cannot list orders', function () {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    });

    test('user sees only their own orders', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Order::create([
            'address' => 'address 1', 'phone' => '01012345678',
            'user_id' => $user1->id, 'total_price' => 100, 'status' => 'pending',
        ]);
        Order::create([
            'address' => 'address 2', 'phone' => '01012345678',
            'user_id' => $user2->id, 'total_price' => 200, 'status' => 'pending',
        ]);

        $response = $this->actingAs($user1)->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    });

    test('returns empty when user has no orders', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    });

    test('orders are paginated (5 per page)', function () {
        $user = User::factory()->create();
        for ($i = 0; $i < 8; $i++) {
            Order::create([
                'address' => "address $i", 'phone' => '01012345678',
                'user_id' => $user->id, 'total_price' => 100, 'status' => 'pending',
            ]);
        }

        $response = $this->actingAs($user)->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    });
});

// ─── SHOW ORDER EDGE CASES ──────────────────────────────────────────

describe('show order edge cases', function () {

    test('unauthenticated user cannot view order', function () {
        $response = $this->getJson('/api/orders/1');

        $response->assertStatus(401);
    });

    test('user cannot view another user\'s order', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user1->id, 'total_price' => 100, 'status' => 'pending',
        ]);

        $response = $this->actingAs($user2)->getJson("/api/orders/{$order->id}");

        $response->assertStatus(404);
    });

    test('show order fails with non-existent id', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/orders/99999');

        $response->assertStatus(404);
    });

    test('show order returns correct structure', function () {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'test']);
        $product = Product::create(['name' => 'test', 'price' => 100, 'category_id' => $category->id]);

        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user->id, 'total_price' => 100, 'status' => 'pending',
        ]);
        OrderItem::create([
            'product_id' => $product->id,
            'order_id' => $order->id,
            'quantity' => 1,
            'price' => 100,
        ]);

        $response = $this->actingAs($user)->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id', 'address', 'phone', 'notes',
                'total_price', 'status', 'user_name', 'items',
            ],
        ]);
    });
});

// ─── STATUS UPDATE EDGE CASES ───────────────────────────────────────

describe('status update edge cases', function () {

    test('unauthenticated user cannot update order status', function () {
        $response = $this->putJson('/api/orders/1', [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(401);
    });

    test('status update fails with non-existent order', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->putJson('/api/orders/99999', [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(404);
    });

    test('status update fails without status field', function () {
        $user = User::factory()->create();
        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user->id, 'total_price' => 100, 'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->putJson("/api/orders/{$order->id}", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    });

    test('status update fails with invalid status value', function () {
        $user = User::factory()->create();
        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user->id, 'total_price' => 100, 'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->putJson("/api/orders/{$order->id}", [
            'status' => 'cancelled',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    });

    test('status update fails with random string status', function () {
        $user = User::factory()->create();
        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user->id, 'total_price' => 100, 'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->putJson("/api/orders/{$order->id}", [
            'status' => 'some-random-status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    });

    test('status can be updated to pending', function () {
        $user = User::factory()->create();
        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user->id, 'total_price' => 100, 'status' => 'confirmed',
        ]);

        $response = $this->actingAs($user)->putJson("/api/orders/{$order->id}", [
            'status' => 'pending',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    });

    test('status can be updated to confirmed', function () {
        $user = User::factory()->create();
        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user->id, 'total_price' => 100, 'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->putJson("/api/orders/{$order->id}", [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'confirmed']);
    });

    test('status can be updated to delivered', function () {
        $user = User::factory()->create();
        $order = Order::create([
            'address' => 'test address', 'phone' => '01012345678',
            'user_id' => $user->id, 'total_price' => 100, 'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->putJson("/api/orders/{$order->id}", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'delivered']);
    });
});
