<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return response()->json([
            'data' => $products,
        ], 200);

    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|min:2',
            'price' => 'required|numeric',
            'description' => 'nullable',
            'image' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'created successfuly',
            'data' => $product,
        ], 201);

    }

    public function update(Request $request, $id)
    {

        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|min:2',
            'price' => 'sometimes|numeric',
            'description' => 'sometimes|nullable',
            'image' => 'sometimes|nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
        ]);

        $product->update($request->only([
            'name', 'price', 'description', 'image', 'category_id',
        ]));

        return response()->json([
            'message' => 'updated successfuly',
            'data' => $product,
        ], 200);

    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json([
            'message' => 'deleted successfuly',
            'data' => $product,
        ], 200);
    }
}
