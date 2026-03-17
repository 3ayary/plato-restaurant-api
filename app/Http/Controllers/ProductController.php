<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(5);

        return ProductResource::collection($products);

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:2',
            'price' => 'required|numeric',
            'description' => 'nullable',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'category_id' => 'required|exists:categories,id',
        ]);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $uploadedFile = Cloudinary()->uploadApi()->upload($request->file('image')->getRealPath());

            $imageUrl = $uploadedFile->offsetGet('secure_url');
        }

        $product = Product::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'category_id' => $validated['category_id'],
            'image' => $imageUrl,
        ]);

        return new ProductResource($product);

    }

    public function update(Request $request, $id)
    {

        $product = Product::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|min:2',
            'price' => 'sometimes|numeric',
            'description' => 'sometimes|nullable',
            'image' => 'sometimes|nullable|image',
            'category_id' => 'sometimes|exists:categories,id',
        ]);


        if ($request->hasFile('image')) {

            if ($product->image) {
                $publicId = pathinfo(parse_url($product->image, PHP_URL_PATH), PATHINFO_FILENAME);

                Cloudinary()->uploadApi()->destroy($publicId);
            }

            $uploadedFile = Cloudinary()->uploadApi()->upload($request->file('image')->getRealPath());

            $data['image'] = $uploadedFile->offsetGet('secure_url');
        }

        $product->update($data);

        return (new ProductResource($product))->response()->setStatusCode(200);

    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return (new ProductResource($product))->response()->setStatusCode(200);
    }
}
