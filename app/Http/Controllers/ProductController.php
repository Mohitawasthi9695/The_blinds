<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return $this->successResponse($products, 'Products retrieved successfully.', 200);
    }
    public function CheckStocks($product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return $this->errorResponse('Product not found.', 404);
        }
        $stocks = $product->stockAvaible->where('status', 1);
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }
        return $this->successResponse($stocks, 'Active stocks retrieved successfully.', 200);
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function store(ProductRequest $request)
    {
        $products = Product::create($request->validated());
        return $this->successResponse($products, 'Product created successfully.', 201);
    }

    public function show($id)
    {
        $Product = Product::find($id);
        if (!$Product) {
            return $this->errorResponse('Product not found.', 404);
        }
        return $this->successResponse($Product, 'Product retrieved successfully.', 200);
    }
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return $this->successResponse($product, 'Product updated successfully.', 200);
    }

    // DELETE /Products/{id} - Delete a Product
    public function destroy($id)
    {
        $Product = Product::find($id);
        if (!$Product) {
            return $this->errorResponse('Product not found.', 404);
        }

        $Product->delete();
        return $this->successResponse([], 'Product deleted successfully.', 200);
    }
}
