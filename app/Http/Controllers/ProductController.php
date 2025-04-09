<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Godown;
use Exception;
use Carbon\Carbon;
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
        $products = Product::where('status', 1)->where('product_category_id', $category_id)->get();
        if ($products->isEmpty()) {
            return $this->errorResponse('No active shadeNo found.', 404);
        }
        log::info($products);
        return $this->successResponse($products, 'Active shadeNo retrieved successfully.', 200);
    }

    public function BarGraphData()
    {
        if ($this->role == 'supervisor') {
            $products = Product::whereHas('stockAvailable')->get();

            $responseData = $products->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'shadeNo' => $product->shadeNo,
                    'product_purchase_shade_no' => $product->purchase_shade_no,
                    'stock_in' => $product->stockAvailable->sum(function ($stock) {
                        return round($stock->quantity, 2);
                    }),
                    'stock_out' => $product->stockAvailable->sum(function ($stock) {
                        return round($stock->out_quantity, 2);
                    }),
                ];
            });
        } elseif ($this->role == 'sub_supervisor') {
            $products = Product::whereHas('godownStock')->get();

            $responseData = $products->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'shadeNo' => $product->shadeNo,
                    'product_purchase_shade_no' => $product->purchase_shade_no,
                    'stock_in' => $product->godownStock->sum(function ($stock) {
                        return round($stock->pcs, 2);
                    }),
                    'stock_out' => $product->godownStock->sum(function ($stock) {
                        return round($stock->out_pcs, 2);
                    }),
                ];
            });
        }

        log::info($responseData);
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
            $seenShadeNos = [];
            $seenPurchaseShadeNos = [];
            foreach ($rows as $index => $row) {
                if ($index === 0)
                    continue;

                if (empty($row[2]) || empty($row[3])) {
                    $invalidRows[] = [
                        'row' => $index + 1,
                        'error' => 'Missing required fields: code or shadeNo'
                    ];
                    continue;
                }

                $productCategory = $row[1];
                $shadeNo = trim($row[3]);
                $purchaseShadeNo = trim($row[4] ?? $shadeNo); // default fallback

                // ✅ Check in current CSV for duplicate shadeNo or purchase_shade_no
                if (
                    in_array($shadeNo, $seenShadeNos) &&
                    in_array($purchaseShadeNo, $seenPurchaseShadeNos)
                ) {
                    $existingRecords[] = [
                        'row' => $index + 1,
                        'shadeNo' => $shadeNo,
                        'purchase_shade_no' => $purchaseShadeNo,
                        'error' => 'Duplicate shadeNo or purchase_shade_no in CSV'
                    ];
                    continue;
                }

                // Add to seen arrays
                $seenShadeNos[] = $shadeNo;
                $seenPurchaseShadeNos[] = $purchaseShadeNo;

                // ✅ Check in database for duplicates
                $existingProduct = Product::where(function ($query) use ($shadeNo, $purchaseShadeNo) {
                    $query->where('shadeNo', $shadeNo)
                        ->orWhere('purchase_shade_no', $shadeNo)
                        ->orWhere('shadeNo', $purchaseShadeNo)
                        ->orWhere('purchase_shade_no', $purchaseShadeNo);
                })->first();

                if ($existingProduct) {
                    $existingRecords[] = [
                        'row' => $index + 1,
                        'shadeNo' => $shadeNo,
                        'purchase_shade_no' => $purchaseShadeNo,
                        'error' => 'Duplicate record exists in database'
                    ];
                    continue;
                }

                $existProductCategory = ProductCategory::where('product_category', $productCategory)->first();
                if (!$existProductCategory) {
                    $invalidRows[] = [
                        'row' => $index + 1,
                        'error' => 'Product Category not found'
                    ];
                    continue;
                }

                $newRecords[] = [
                    'product_category_id' => $existProductCategory->id,
                    'name' => $row[2],
                    'shadeNo' => $shadeNo,
                    'purchase_shade_no' => $purchaseShadeNo,
                    'date' => $row[5] ?? Carbon::today(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($newRecords)) {
                Product::insert($newRecords);
            }
            return response()->json([
                'message' => 'File processed successfully.',
                'createdCount' => count($newRecords),
                'duplicates' => $existingRecords,
                'invalidRows' => $invalidRows,
            ], 201);
        } catch (Exception $e) {
            log::error('CSV Processing Error: ', [
                'error' => $e->getMessage(),
                'file' => $request->file('csv_file')->getClientOriginalName(),
            ]);

            return response()->json([
                'error' => 'Failed to process file',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function store(ProductRequest $request)
    {
        $products= Product::where('product_category_id',$request->product_category_id)->where('shadeNo', $request->shadeNo)
            ->where('purchase_shade_no', $request->purchase_shade_no)
            ->first();
            if($products)
            {
                return $this->errorResponse('Product already exists.', 409);
            }
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
        if (!$product) {
            return $this->errorResponse('Product not found.', 404);
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'product_category_id' => 'required|exists:product_categories,id',
            'shadeNo' => 'required|string|max:255',
            'purchase_shade_no' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);
        $product->update($data);
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
