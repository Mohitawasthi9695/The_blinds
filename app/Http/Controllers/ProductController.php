<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Godown;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends ApiController
{
    public function index()
    {
        $products = Product::with('ProductCategory:id,product_category')->get();
        return $this->successResponse($products, 'Products retrieved successfully.', 200);
    }
    public function ProductShadeNo($category_id)
    {
        $products = Product::where('status', 1)->where('product_category_id',$category_id)->get();
        if($products->isEmpty()){
            return $this->errorResponse('No active shadeNo found.', 404);
        }
        return $this->successResponse($products, 'Active shadeNo retrieved successfully.', 200);
    }

    public function BarGraphData()
    {
        $products = Product::whereHas('stockAvailable')->with('stockOutDetails')->get();

        $responseData = $products->map(function ($product) {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'shadeNo' => $product->shadeNo,
                'product_purchase_shade_no' => $product->purchase_shade_no,
                'stock_in' => $product->stockAvailable->sum(function ($stock) {
                    return round($stock->length * $stock->width * 10.7639, 2);
                }),
                'stock_out' => $product->stockOutDetails->sum(function ($stock) {
                    return round($stock->out_length * $stock->out_width * 10.7639, 2);
                }),
            ];
        });

        return $this->successResponse($responseData, 'Bar graph data retrieved successfully.', 200);
    }

    

    public function ProductCsv(Request $request)
    {
        log::info($request->all());

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

                if (empty($row[2]) || empty($row[3])) {
                    $invalidRows[] = [
                        'row'   => $index + 1,
                        'error' => 'Missing required fields: code or shadeNo'
                    ];
                    continue;
                }
                $productCategory = $row[1];
                log::info($productCategory);
                $shadeNo = $row[3];
                $purchaseShadeNo = $row[4] ?? null;
                $existProductCategory = ProductCategory::where('product_category', $productCategory)
                    ->first();
                if (!$existProductCategory) {
                    $invalidRows[] = [
                        'row'   => $index + 1,
                        'error' => 'Product Category not found'
                    ];
                    continue;
                }
                $existingProduct = Product::Where('shadeNo', $shadeNo)
                    ->first();

                if ($existingProduct) {
                    $existingRecords[] = [
                        'row'   => $index + 1,
                        'shadeNo' => $shadeNo,
                        'error' => 'Duplicate record exists in the database'
                    ];
                    continue;
                }
                $newRecords[] = [
                    'product_category_id'=> $existProductCategory->id,
                    'name'           => $row[2],
                    'shadeNo'           => $shadeNo,
                    'purchase_shade_no' => $purchaseShadeNo,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }
            if (!empty($newRecords)) {
                Product::insert($newRecords);
            }
            return response()->json([
                'message'       => 'File processed successfully.',
                'createdCount'  => count($newRecords),
                'duplicates'    => $existingRecords,
                'invalidRows'   => $invalidRows,
            ], 201);
        } catch (Exception $e) {
            log::error('CSV Processing Error: ', [
                'error'    => $e->getMessage(),
                'file'     => $request->file('csv_file')->getClientOriginalName(),
            ]);

            return response()->json([
                'error'   => 'Failed to process file',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
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
