<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function getProducts()
    {

            $category = Category::paginate(10);

            return CategoryResource::collection($category);

    }

    public function createCategory(Request $request)
    {
      $validated =   $request->validate([
            'name' => 'required|min:2',
        ]);

        $category = Category::create($validated);

        return new CategoryResource($category);
    }

    public function update(Request $request, $id)
    {

        $update = $request->validate([
            'name' => 'required|min:2',
        ]);

        $category = Category::findOrFail($id);

        $category->update($update);

        return new CategoryResource($category);

    }

    public function destroy($id)
    {

        $category = Category::findOrFail($id);

        $category->delete();

        return new CategoryResource($category);

    }
}
