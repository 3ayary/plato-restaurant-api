<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'address' => 'required|string|min:8',
            'phone' => 'required|string|min:9',
            'notes' => 'string|nullable',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalPrice = 0;

        $order = Order::create([
            'address' => $request->address,
            'phone' => $request->phone,
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
            'total_price' => 0,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);

            $orderItem = OrderItem::create([
                'product_id' => $product->id,
                'order_id' => $order->id,
                'quantity' => $item['quantity'],
                'price' => $product->price * $item['quantity'],
            ]);

            $totalPrice += $orderItem->price;
        }

        $order->update(['total_price' => $totalPrice]);

        return response()->json([
            'message' => 'created successfuly',
            'data' => $order,
        ], 201);
    }

    public function index(Request $request)
    {

        $orders = Order::where('user_id', $request->user()->id)->get();

        return response()->json([
            'data' => $orders,
        ], 200);
    }

    public function show(Request $request, $orderId)
    {
        $order = Order::with('items')->where('id', $orderId)->where('user_id', $request->user()->id)->firstOrFail();

        return response()->json([
            'data' => $order,
        ], 200);
    }

    public function statusUpdate(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $request->validate([
            'status' => 'required|string|in:pending,confirmed,delivered',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json(['data' => $order]);
    }
}
