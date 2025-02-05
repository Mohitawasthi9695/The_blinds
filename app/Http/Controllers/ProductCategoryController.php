<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = ProductCategory::all();
        return $this->successResponse($products, 'Products retrieved successfully.', 200);
    }

    public function store(Request $request)
    {
        $productsCategory = $request->validate(['product_category' => 'required|string|Max:255']);
        $products = ProductCategory::create($productsCategory);
        return $this->successResponse($products, 'Product created successfully.', 201);
    }

    public function show($id)
    {
        $ProductCategory = ProductCategory::find($id);
        if (!$ProductCategory) {
            return $this->errorResponse('ProductCategory not found.', 404);
        }
        return $this->successResponse($ProductCategory, 'ProductCategory retrieved successfully.', 200);
    }
    public function update(Request $request, $id)
    {
        $product = ProductCategory::findOrFail($id);
        $product->update($request->all());
        return $this->successResponse($product, 'ProductCategory updated successfully.', 200);
    }

    // DELETE /ProductCategorys/{id} - Delete a ProductCategory
    public function destroy($id)
{
    // Find the ProductCategory by ID
    $ProductCategory = ProductCategory::find($id);
    if (!$ProductCategory) {
        return $this->errorResponse('ProductCategory not found.', 404);
    }
    $relatedProducts = Product::where('product_category_id', $id)->exists();
    if ($relatedProducts) {
        return $this->errorResponse('Cannot delete ProductCategory because related products exist.', 400);
    }
    $ProductCategory->delete();
    return $this->successResponse([], 'ProductCategory deleted successfully.', 200);
}

}
