<?php

namespace App\Http\Controllers;

use App\Models\ProductAccessory;
use Illuminate\Http\Request;

class ProductAccessoryController extends ApiController
{
    public function index()
    {
        $products = ProductAccessory::with('productCategory')->get();
        if (!$products) {
            return $this->errorResponse('ProductAccessory not found.', 404);
        }
        $products=$products->map(function ($product) {
            return [
                'id' => $product->id,
                'product_category' => $product->productCategory->product_category,
                'accessory_name' => $product->accessory_name,
                'status' => $product->status,
            ];
        });
        return $this->successResponse($products, 'ProductAccessory retrieved successfully.', 200);
    }


    public function GetCategoryAccessory($id)
    {
        $products = ProductAccessory::where('product_category_id', $id)->get();
        if (!$products) {
            return $this->errorResponse('ProductAccessory not found.', 404);
        }
        return $this->successResponse($products, 'ProductAccessory retrieved successfully.', 200);
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
        return $this->successResponse($products, 'ProductAccessory created successfully.', 201);
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
        $ProductAccessory = ProductAccessory::findOrFail($id);
        $ProductAccessory->update($request->all());
        return $this->successResponse($ProductAccessory, 'ProductAccessory updated successfully.', 200);
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
