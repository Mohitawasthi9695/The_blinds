<?php

namespace App\Http\Controllers;

use App\Models\ProductAccessory;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Exception;

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
    public function ProductAccessoryCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        try {
            $data = Excel::toArray([], $request->file('csv_file'));

            if (empty($data) || !isset($data[0])) {
                return response()->json(['error' => 'File is empty or invalid'], 422);
            }

            $rows = $data[0];
            $createdItems = [];
            $invalidRows = [];
            $existingRecords = [];
            $newRecords = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                if (empty($row[1]) || empty($row[2])) {
                    $invalidRows[] = [
                        'row'   => $index + 1,
                        'error' => 'Missing required fields: Category or '
                    ];
                    continue;
                }
                $productCategory = $row[1];
                $accessory = $row[2];
                $existProductCategory = ProductCategory::where('product_category', $productCategory)
                    ->first();
                if (!$existProductCategory) {
                    $invalidRows[] = [
                        'row'   => $index + 1,
                        'error' => 'Product Category not found'
                    ];
                    continue;
                }
                $existingProductAccessory = ProductAccessory::Where('accessory_name', $accessory)
                    ->first();

                if ($existingProductAccessory) {
                    $existingRecords[] = [
                        'row'   => $index + 1,
                        'accessory_name' => $accessory,
                        'error' => 'Duplicate record exists in the database'
                    ];
                    continue;
                }
                $newRecords[] = [
                    'product_category_id'=> $existProductCategory->id,
                    'accessory_name'           => $row[2],
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            if (!empty($newRecords)) {
                ProductAccessory::insert($newRecords);
            }
            return response()->json([
                'message'       => 'File processed successfully.',
                'createdCount'  => count($newRecords),
                'duplicates'    => $existingRecords,
                'invalidRows'   => $invalidRows,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error'   => 'Failed to process file',
                'message' => $e->getMessage(),
            ], 500);
        }
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
