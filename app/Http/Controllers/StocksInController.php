<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInRequest;
use App\Models\AvailableStock;
use App\Models\Product;
use App\Models\StockInvoice;
use App\Models\StocksIn;
use League\Csv\Reader;
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

        if (!is_array($validatedData)) {
            return $this->errorResponse('Invalid data format. Expected an array of records.', 422);
        }

        try {
            $createdItems = [];

            foreach ($validatedData as $data) {
                if (isset($data['type']) && $data['type'] === 'roll') {
                    if (isset($data['length']) && isset($data['width'])) {
                        $data['area'] = $data['length'] * $data['width'];
                        if (isset($data['unit']) && $data['unit'] === 'meter' && isset($data['qty'])) {
                            $data['area_sq_ft'] = $data['area'] * 10.764;
                        } else {
                            $data['area_sq_ft'] = null;
                        }
                    } else {
                        return $this->errorResponse('Length and width are required for type "roll".', 422);
                    }
                }

                $createdStockIn = StocksIn::create($data); // Capture the created StocksIn object
                $createdItems[] = $createdStockIn;
                $existingStock = AvailableStock::where('product_id', $data['product_id'])
                    ->where('type', $data['type'])
                    ->where('length', $data['length'])
                    ->where('width', $data['width'])
                    ->where('unit', $data['unit'])
                    ->first();

                if ($existingStock) {
                    // Update the existing record's quantity and area
                    $existingStock->update([
                        'qty' => $existingStock->qty + $data['qty'],
                        'area' => $existingStock->area + $data['area'],
                        'area_sq_ft' => $existingStock->area_sq_ft + $data['area_sq_ft'],
                    ]);
                } else {
                    // Create a new record in AvailableStock
                    AvailableStock::create([
                        'stock_ins_id'=> $createdStockIn->id,
                        'product_id' => $data['product_id'],
                        'type' => $data['type'],
                        'length' => $data['length'],
                        'width' => $data['width'],
                        'area' => $data['area'],
                        'area_sq_ft' => $data['area_sq_ft'],
                        'qty' => $data['qty'],
                        'unit' => $data['unit'],
                        'rack' => $data['rack'] ?? null,
                    ]);
                }
            }

            return $this->successResponse($createdItems, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries.', 500, $e->getMessage());
        }
    }
    public function storeFromCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        try {
            $csv = Reader::createFromPath($request->file('csv_file')->getRealPath(), 'r');
            $csv->setHeaderOffset(0);

            $records = $csv->getRecords();

            $createdItems = [];
            foreach ($records as $row) {
                if (!isset($row['shadeNo'])) {
                    return response()->json(['error' => 'shadeNo is missing in a row'], 422);
                }
                if (!isset($row['invoice_no'])) {
                    return response()->json(['error' => 'invoice_no is missing in a row'], 422);
                }

                $product = Product::where('shadeNo', $row['shadeNo'])->first();
                $Invoice = StockInvoice::where('invoice_no', $row['invoice_no'])->first();

                if (!$product) {
                    return response()->json(['error' => "Product with shadeNo {$row['shadeNo']} not found"], 422);
                }
                if (!$Invoice) {
                    return response()->json(['error' => "Invoice with invoice_no {$row['invoice_no']} not found"], 422);
                }

                // Calculate area for type "roll"
                if (isset($row['type']) && $row['type'] === 'roll') {
                    if (isset($row['length']) && isset($row['width'])) {
                        $row['area'] = $row['length'] * $row['width'];

                        if (isset($row['unit']) && $row['unit'] === 'meter' && isset($row['qty'])) {
                            $row['area_sq_ft'] = $row['area'] * $row['qty'] * 10.764;
                        } else {
                            $row['area_sq_ft'] = null;
                        }
                    } else {
                        return response()->json(['error' => 'Length and width are required for type "roll".'], 422);
                    }
                }
                $data = [
                    'product_id' => $product->id,
                    'invoice_id' => $Invoice->id,
                    'invoice_no' => $row['invoice_no'] ?? null,
                    'lot_no'  => $row['lot_no'] ?? null,
                    'type'       => $row['type'] ?? null,
                    'length'     => $row['length'] ?? null,
                    'width'      => $row['width'] ?? null,
                    'area'       => $row['area'] ?? null,
                    'area_sq_ft' => $row['area_sq_ft'] ?? null,
                    'qty'        => $row['qty'] ?? null,
                    'unit'       => $row['unit'] ?? null,
                    'shadeNo'    => $row['shadeNo'],
                ];

                $createdItem = StocksIn::create($data);
                $createdItems[] = $createdItem;
                $existingStock = AvailableStock::where('product_id', $data['product_id'])
                    ->where('type', $data['type'])
                    ->where('length', $data['length'])
                    ->where('width', $data['width'])
                    ->where('unit', $data['unit'])
                    ->first();

                if ($existingStock) {
                    // Update the existing record's quantity and area
                    $existingStock->update([
                        'qty' => $existingStock->qty + $data['qty'],
                        'area' => $existingStock->area + $data['area'],
                        'area_sq_ft' => $existingStock->area_sq_ft + $data['area_sq_ft'],
                    ]);
                } else {
                    // Create a new record in AvailableStock
                    AvailableStock::create([
                        'stock_ins_id'=> $createdItem->id,
                        'product_id' => $data['product_id'],
                        'type' => $data['type'],
                        'length' => $data['length'],
                        'width' => $data['width'],
                        'area' => $data['area'],
                        'area_sq_ft' => $data['area_sq_ft'],
                        'qty' => $data['qty'],
                        'unit' => $data['unit'],
                        'rack' => $data['rack'] ?? null,
                    ]);
                }
            }

            return response()->json(['success' => 'Stock entries created successfully', 'data' => $createdItems], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to process CSV file', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $stocks = StocksIn::with(['stockProduct', 'stockInvoice'])
            ->where('invoice_id', $id)
            ->get();
        return response()->json($stocks);
    }

    public function update(StockInRequest $request, $id)
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

        return $this->successResponse(['message' => 'Stock entry deleted successfully'], 200);
    }
}
