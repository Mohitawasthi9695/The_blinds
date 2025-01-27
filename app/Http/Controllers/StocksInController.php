<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInRequest;
use App\Http\Requests\StockInUpdate;
use App\Models\Product;
use App\Models\StockInvoice;
use App\Models\StocksIn;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StocksInController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = StocksIn::with(['stockProduct', 'stockInvoice'])->get();
        return response()->json($stocks);
    }

    public function CategoryRollStocks()
    {
        $stocks = StocksIn::with(['stockProduct'])
        ->where('type','roll')
        ->get();

    return response()->json($stocks);
    }
    public function CategoryBoxStocks()
    {
        $stocks = StocksIn::with(['stockProduct'])
        ->where('type','box')
        ->get();

    return response()->json($stocks);
    }


    /**
     * Truncate the StocksIn table.
     */

    public function truncateStockOutDetails()
    {
        StocksIn::truncate();

        return response()->json([
            'message' => 'StockOutdetails table has been truncated successfully.'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockInRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $createdItems = [];

            foreach ($validatedData as $data) {
                $data['user_id'] =  Auth::id();;
                $createdItems = StocksIn::create($data);
            }

            return $this->successResponse($createdItems, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries.', 500, $e->getMessage());
        }
    }


    public function storeFromCsv(Request $request)
    {
        log::info($request->all());
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);
        try {
            $data = Excel::toArray([], $request->file('csv_file'));

            if (empty($data) || !isset($data[0])) {
                return response()->json(['error' => 'File is empty or invalid'], 422);
            }
            $rows = $data[0];
            $createdItems = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }

                if (empty($row[1]) || empty($row[2])) {
                    return response()->json(['error' => 'Required fields shadeNo or invoice_no are missing'], 422);
                }

                $shadeNo = $row[3];
                $invoiceNo = $row[1];
                $product = Product::where('shadeNo', $shadeNo)->first();
                $invoice = StockInvoice::where('invoice_no', $invoiceNo)->first();

                if (!$product) {
                    return response()->json(['error' => "Product with shadeNo {$shadeNo} not found"], 422);
                }
                if (!$invoice) {
                    return response()->json(['error' => "Invoice with invoice_no {$invoiceNo} not found"], 422);
                }
                $stock_code = StocksIn::where('product_id', [$product->id])
                    ->orderBy('id', 'desc')
                    ->first();
                $data = [
                    'product_id' => $product->id,
                    'invoice_id' => $invoice->id,
                    'user_id' =>  Auth::id(),
                    'invoice_no' => $invoiceNo,
                    'lot_no'  => $row[2] ?? null,
                    'width'     => $row[4] ?? null,
                    'length'      => $row[5] ?? null,
                    'rack'        => $row[7] ?? null,
                    'unit'       => $row[6] ?? null,
                    'type'       => $row[8] ?? null,
                    'quantity'    => $row[9] ?? null,
                    'warehouse' => $row[10] ?? null
                ];

                $createdItem = StocksIn::create($data);
            }
            return $this->successResponse($createdItem, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process file', 'message' => $e->getMessage()], 500);
        }
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
                'lot_no' => $stock->lot_no,
                'out_length' => $stock->length,
                'out_width' => $stock->width,
                'unit' => $stock->unit,
                // 'area_sq_ft' => round($stock->length * $stock->width * 10.7639),
                // 'area' => $stock->length * $stock->width,
                'product_type' => $stock->type,
                'out_quantity' => $stock->quantity-$stock->out_quantity,
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
    public function show($id)
    {
        $stocks = StocksIn::with(['stockProduct', 'stockInvoice'])
            ->where('invoice_id', $id)
            ->get();
            log::info($stocks);
        return response()->json($stocks);
    }

    public function update(StockInUpdate $request, $id)
    {
        $stock = StocksIn::findOrFail($id);

        $validatedData = $request->validated();
        $stock->update($validatedData);
        return $this->successResponse($stock, 'Stock entry updated successfully.', 200);
    }

    public function destroy($id)
    {
        $stock = StocksIn::findOrFail($id);
        $stock->delete();
        return $this->successResponse([], 'Stock entry deleted successfully.', 200);
    }
}
