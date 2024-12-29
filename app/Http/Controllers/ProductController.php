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
    public function AvailableProducts()
    {
        $products = Product::whereHas('stockAvailable', function ($query) {
            $query->where('status', 1);
        })->get();
        return $this->successResponse($products, 'Active products retrieved successfully.', 200);
    }
    public function AvailableStocks()
    {
        $stocks = Product::whereHas('stockAvailable', function ($query) {
            $query->where('status', 1);
        })->with('stockAvailable')->get();
        return $this->successResponse($stocks, 'Active stocks retrieved successfully.', 200);
    }
    
    public function CheckStocks($product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return $this->errorResponse('Product not found.', 404);
        }
        $stocks = $product->stockAvailable->where('status', 1);
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }
        $responseData = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'product_id' => $stock->product_id,
                'length' => $stock->length,
                'width' => $stock->width,
                'unit' => $stock->unit,
                'type' => $stock->type,
                'qty' => $stock->qty,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name,
                'product_code' => $stock->products->code,
                'product_shadeNo' => $stock->products->shadeNo,
                'product_purchase_shade_no' => $stock->products->purchase_shade_no,
            ];
        });
        return $this->successResponse($responseData, 'Active stocks retrieved successfully.', 200);
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
