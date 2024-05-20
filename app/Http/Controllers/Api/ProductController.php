<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return $this->sendResponse(['products' => Product::get()], 'Success get products data!');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => "required",
            'price' => 'required|min:1',
            'category_id' => 'required',
            'expired_at' => 'required|date_format:Y-m-d',
            'image' => 'required|mimes:png,jpg,jpeg'
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error.", $validator->errors());
        }

        $category = Category::where(['name' => $request->category_id])->first();
        if (!$category) {
            return $this->sendError("Category not found", [], 404);
        }

        try {
            //code...
            $product = new Product;
            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->category_id = $category->id;
            $product->expired_at = $request->expired_at;
            $product->modified_by = $request->user()->email;

            $path = Storage::disk('public')->put('products', $request->image);
            $product->image = $path;
            $product->save();

            return $this->sendResponse(['id' => $product->id], 'Product created successfully!', 201);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError("Something went wrong", [], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $product = Product::where(['id' => $id])->first();

        if (!$product) return $this->sendError("Product not found", [], 404);

        return $this->sendResponse(['product' => $product], 'Success get data product');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => "required",
            'price' => 'required|min:1',
            'category_id' => 'required',
            'expired_at' => 'required|date',
            'image' => 'mimes:png,jpg,jpeg'
        ]);

        if ($validator->fails()) {
            return $this->sendError("Validation Error.", $validator->errors());
        }

        $category = Category::where(['name' => $request->category_id])->first();
        if (!$category) {
            return $this->sendError("Category not found", [], 404);
        }

        $product = Product::where(['name' => $id])->first();

        if (!$product) return $this->sendError("Product not found", [], 404);

        try {
            //code...
            $product->name = $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->category_id = $category->id;
            $product->expired_at = $request->expired_at;
            $product->modified_by = $request->user()->email;

            if ($request->hasFile('image')) {
                $path = Storage::disk('public')->put('products', $request->image);
                $product->image = $path;
            }
            $product->save();

            return $this->sendResponse(null, 'Product updated successfully!', 201);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError("Something went wrong", [], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $product = Product::where(['id' => $id])->first();

        if (!$product) return $this->sendError("Product not found", [], 404);

        $product->delete();

        return $this->sendResponse(null, 'Product data deleted successfullyl!');
    }
}
