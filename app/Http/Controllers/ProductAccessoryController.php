<?php

namespace App\Http\Controllers;

use App\Models\ProductAccessory;
use Illuminate\Http\Request;

class ProductAccessoryController extends ApiController
{
    public function index()
    {
        $products = ProductAccessory::all();
        return $this->successResponse($products, 'Products retrieved successfully.', 200);
    }

    public function store(Request $request)
    {
        $productsCategory = $request->validate(
            [
                'product_category_id' => 'required|numeric|exists:product_categories,id',
                'accessory_name' => 'required|string|max:255',
            ]
        );
        $products = ProductAccessory::create($productsCategory);
        return $this->successResponse($products, 'Product created successfully.', 201);
    }

    public function show($id)
    {
        $ProductAccessory = ProductAccessory::find($id);
        if (!$ProductAccessory) {
            return $this->errorResponse('ProductAccessory not found.', 404);
        }
        return $this->successResponse($ProductAccessory, 'ProductAccessory retrieved successfully.', 200);
    }
    
    public function update(Request $request, $id)
    {
        $product = ProductAccessory::findOrFail($id);
        $product->update($request->all());
        return $this->successResponse($product, 'ProductAccessory updated successfully.', 200);
    }

    // DELETE /ProductAccessorys/{id} - Delete a ProductAccessory
    public function destroy($id)
    {
        $ProductAccessory = ProductAccessory::find($id);
        if (!$ProductAccessory) {
            return $this->errorResponse('ProductAccessory not found.', 404);
        }

        $ProductAccessory->delete();
        return $this->successResponse([], 'ProductAccessory deleted successfully.', 200);
    }
}
