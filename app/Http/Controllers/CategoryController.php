<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getProducts()
    {

        $category = Category::all();

        return response()->json(['data' => $category], 200);

    }

    public function createCategory(Request $request)
    {
      $validated =   $request->validate([
            'name' => 'required|min:2',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'created successfuly',
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, $id)
    {

        $update = $request->validate([
            'name' => 'required|min:2',
        ]);

        $category = Category::findOrFail($id);

        $category->update($update);

        return response()->json([
            'message' => 'updated successfuly',
            'data' => $category,
        ],200);

    }

    public function destroy($id)
    {

        $category = Category::findOrFail($id);

        $category->delete();

        return response()->json([
            'message' => 'deleted successfuly',
            'data' => $category,
        ],200);

    }
}
