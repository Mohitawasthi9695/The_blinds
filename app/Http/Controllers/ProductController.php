<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        Log::info($products);
        return $this->successResponse($products, 'Active products retrieved successfully.', 200);
    }
    public function AvailableStocks()
    {
        $stocks = Product::whereHas('stockAvailable', function ($query) {
            $query->where('qty', '>', 0)
                ->where('status', 1);
        })->with(['stockAvailable' => function ($query) {
            $query->where('qty', '>', 0)
                ->where('status', 1);
        }])->get();

        return $this->successResponse($stocks, 'Active stocks retrieved successfully.', 200);
    }

    public function CheckStocks($product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return $this->errorResponse('Product not found.', 404);
        }
        $stocks = $product->stockAvailable()->where('status', 1)->with('products')->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($stock) {
            return [
                'stock_available_id' => $stock->id,
                'product_id' => $stock->product_id,
                'stock_code' => $stock->products->shadeNo . '-' . $stock->stock_code,
                'lot_no' => $stock->lot_no,
                'out_length' => $stock->available_height,
                'out_width' => $stock->available_width,
                'unit' => $stock->unit,
                'area_sq_ft'=>$stock->length*$stock->width*10.7639,
                'area'=>$stock->length*$stock->width,
                'product_type' => $stock->type,
                'out_quantity' => $stock->qty,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? 'N/A',
                'product_code' => $stock->products->code ?? 'N/A',
                'product_shadeNo' => $stock->products->shadeNo ?? 'N/A',
                'product_purchase_shade_no' => $stock->products->purchase_shade_no ?? 'N/A',
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
