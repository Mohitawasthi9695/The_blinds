<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInRequest;
use App\Http\Requests\StockInUpdate;
use App\Models\Product;
use App\Models\StockInvoice;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use App\Models\StocksIn;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    /**
     * Store a newly created resource in storage.
     */
    public function store(StockInRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $createdItems = [];

            foreach ($validatedData as $data) {
                $data['available_width'] = $data['width'];
                $data['available_height'] = $data['length'];
                $createdItems = StocksIn::create($data);
            }

            return $this->successResponse($createdItems, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries.', 500, $e->getMessage());
        }
    }

    public function storeFromCsv(Request $request)
    {
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
                    'invoice_no' => $invoiceNo,
                    'lot_no'  => $row[2] ?? null,
                    'available_width' => $row[4] ?? null,
                    'available_height' => $row[5] ?? null,
                    'width'     => $row[4] ?? null,
                    'length'      => $row[5] ?? null,
                    'rack'        => $row[7] ?? null,
                    'unit'       => $row[6] ?? null,
                    'type'       => $row[8] ?? null,
                    'qty'        => 1 ?? null,
                ];

                $createdItem = StocksIn::create($data);
            }
            return $this->successResponse($createdItem, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process file', 'message' => $e->getMessage()], 500);
        }
    }

    public function approveStockOut(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:1,2', 
        ]);
        $status = $request->status;

        $stockoutInvoice = StockoutInovice::where('id', $id)
            ->where('status', '0') 
            ->first();

        if (!$stockoutInvoice) {
            return $this->errorResponse('Stock-out invoice not found or already processed.', 404);
        }

        $stockoutDetails = StockOutDetail::where('stockout_inovice_id', $stockoutInvoice->id)
            ->get();

        foreach ($stockoutDetails as $stockout) {
            $availableStock = StocksIn::where('id', $stockout->stock_in_id)
                ->where('status', '1') 
                ->first();

            if (!$availableStock) {
                return $this->errorResponse("Stock-in entry for ID {$stockout->stock_in_id} not found.", 404);
            }

            if ($stockout->out_length > $availableStock->available_height || $stockout->out_width > $availableStock->width) {
                return $this->errorResponse("Insufficient stock available for Stock-in ID {$stockout->stock_in_id}.", 400);
            }
        }

        // Process the transaction
        DB::transaction(function () use ($stockoutInvoice, $stockoutDetails, $status) {
            foreach ($stockoutDetails as $stockout) {
                $availableStock = StocksIn::find($stockout->stock_in_id);

                if ($status == 1) { 
                    $remainingLength = $availableStock->available_height - $stockout->out_length;
                    $remainingWidth = $availableStock->available_width - $stockout->out_width;

                    $availableStock->update([
                        'available_height' => max($remainingLength, 0),
                        'available_width' => max($remainingWidth, 0),
                        'status' => ($remainingLength <= 0 || $remainingWidth <= 0) ? '0' : '1', 
                    ]);

                    $stockout->update(['status' => 1]); 
                } elseif ($status == 2) { 
                    $stockout->update(['status' => 2]); 
                }
            }

            $stockoutInvoice->update(['status' => $status]);
        });

        return $this->successResponse([
            'stock_out_invoice' => $stockoutInvoice,
            'stock_out_details' => $stockoutDetails,
        ], 'Stock-out processed successfully.', 200);
    }


    public function show($id)
    {
        $stocks = StocksIn::with(['stockProduct', 'stockInvoice'])
            ->where('invoice_id', $id)
            ->get();
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
