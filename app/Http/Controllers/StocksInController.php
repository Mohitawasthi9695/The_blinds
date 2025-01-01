<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInRequest;
use App\Http\Requests\StockInUpdate;
use App\Models\Product;
use App\Models\StockInvoice;
use App\Models\StocksIn;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

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
                    'qty'        => $row[7] ?? null,
                    'unit'       => $row[6] ?? null,
                    'type'       => $row[8] ?? null,
                ];

                $createdItem = StocksIn::create($data);
            }
            return $this->successResponse($createdItem, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process file', 'message' => $e->getMessage()], 500);
        }
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
