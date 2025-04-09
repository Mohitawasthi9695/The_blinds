<?php

namespace App\Http\Controllers;

use App\Models\ProductAccessory;
use App\Models\ProductCategory;
use Carbon\Carbon;
use DateTime;
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
        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'product_category' => $product->productCategory->product_category,
                'accessory_name' => $product->accessory_name,
                'remark' => $product->remark,
                'date' => $product->date,
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
                'remark' => 'required|string|max:255',
                'date' => 'required|date',
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

            // Track for file-level duplicate entries
            $fileDuplicatesCheck = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue; // Skip header
                }

                if (empty($row[1]) || empty($row[2])) {
                    $invalidRows[] = [
                        'row' => $index + 1,
                        'error' => 'Missing required fields: Category or Accessory'
                    ];
                    continue;
                }

                $productCategory = trim($row[1]);
                $accessory = trim($row[2]);

                $existProductCategory = ProductCategory::where('product_category', $productCategory)->first();

                if (!$existProductCategory) {
                    $invalidRows[] = [
                        'row' => $index + 1,
                        'error' => 'Product Category not found'
                    ];
                    continue;
                }

                // Check if this accessory already exists in this category in the DB
                $existingProductAccessory = ProductAccessory::where('accessory_name', $accessory)
                    ->where('product_category_id', $existProductCategory->id)
                    ->first();

                if ($existingProductAccessory) {
                    $existingRecords[] = [
                        'row' => $index + 1,
                        'accessory_name' => $accessory,
                        'error' => 'Duplicate record exists in the database'
                    ];
                    continue;
                }

                // Check for duplicates within the uploaded file
                $fileKey = strtolower($existProductCategory->id . '_' . $accessory);
                if (isset($fileDuplicatesCheck[$fileKey])) {
                    $invalidRows[] = [
                        'row' => $index + 1,
                        'error' => 'Duplicate accessory in the uploaded file for the same category'
                    ];
                    continue;
                }

                $fileDuplicatesCheck[$fileKey] = true;

                // Add to new records
                $newRecords[] = [
                    'product_category_id' => $existProductCategory->id,
                    'accessory_name' => $accessory,
                    'remark' => $row[3] ?? null,
                    'date' => isset($row[4]) && !empty($row[4])
                        ? DateTime::createFromFormat('d/m/Y', $row[4])->format('Y-m-d')
                        : Carbon::today(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($newRecords)) {
                ProductAccessory::insert($newRecords);
            }

            return response()->json([
                'message' => 'File processed successfully.',
                'createdCount' => count($newRecords),
                'duplicates' => $existingRecords,
                'invalidRows' => $invalidRows,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to process file',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        $ProductAccessory = ProductAccessory::with('productCategory')->find($id);
        if (!$ProductAccessory) {
            return $this->errorResponse('ProductAccessory not found.', 404);
        }
        $ProductAccessory = $ProductAccessory->map(function ($product) {
            return [
                'id' => $product->id,
                'product_category' => $product->productCategory->product_category,
                'accessory_name' => $product->accessory_name,
                'remark' => $product->remark,
                'date' => $product->date,
                'status' => $product->status,
            ];
        });
        return $this->successResponse($ProductAccessory, 'ProductAccessory retrieved successfully.', 200);
    }

    public function update(Request $request, $id)
    {
        $ProductAccessory = ProductAccessory::findOrFail($id);
        $request->validate(
            [
                'product_category_id' => 'required|numeric|exists:product_categories,id',
                'accessory_name' => 'required|string|max:255',
                'remark' => 'required|string|max:255',
                'date' => 'required|date',
            ]
        );
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
