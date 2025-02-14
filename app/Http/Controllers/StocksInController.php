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
use Illuminate\Support\Facades\DB;

class StocksInController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = StocksIn::with(relations: ['products', 'products.ProductCategory'])->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'invoice_id' => $stock->invoice_id,
                'product_id' => $stock->product_id,
                'date' => $stock->stockInvoice->date,
                'lot_no' => $stock->lot_no,
                'invoice_no' => $stock->invoice_no,
                'length' => $stock->length??'',
                'width' => $stock->width??'',
                'unit' => $stock->unit,
                'type' => $stock->type,
                'pcs' => $stock->pcs,
                'quantity' => $stock->quantity,
                'out_quantity' => $stock->out_quantity,
                'rack' => $stock->rack,
                'warehouse' => $stock->warehouse,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->name ?? null,
            ];
        });
        return response()->json($stocks);
    }
    public function RollerStocks()
    {
        $stocks = StocksIn::with(relations: ['products', 'stockInvoice','supplier'])
            ->where('product_category_id', 1)
            ->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'invoice_id' => $stock->invoice_id,
                'product_id' => $stock->product_id,
                'date' => $stock->stockInvoice->date,
                'supplier' => $stock->stockInvoice->supplier->name,
                'lot_no' => $stock->lot_no,
                'invoice_no' => $stock->invoice_no,
                'length' => $stock->length,
                'width' => $stock->width,
                'length_unit' => $stock->length_unit,
                'width_unit' => $stock->width_unit,
                'type' => $stock->type,
                'quantity' => $stock->quantity,
                'out_quantity' => $stock->out_quantity,
                'rack' => $stock->rack,
                'warehouse' => $stock->warehouse,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return response()->json($stocks);
    }
    public function WoodenStocks()
    {
        $stocks = StocksIn::with(relations: ['products', 'stockInvoice','supplier'])
            ->where('product_category_id', 2)
            ->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'invoice_id' => $stock->invoice_id,
                'product_id' => $stock->product_id,
                'date' => $stock->stockInvoice->date,
                'supplier' => $stock->stockInvoice->supplier->name,
                'lot_no' => $stock->lot_no,
                'invoice_no' => $stock->invoice_no,
                'length' => $stock->length,
                'width' => $stock->width,
                'length_unit' => $stock->length_unit,
                'width_unit' => $stock->width_unit,
                'pcs' => $stock->pcs,
                'quantity' => $stock->quantity,
                'out_quantity' => $stock->out_quantity,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return response()->json($stocks);
    }
    public function VerticalStocks()
    {
        $stocks = StocksIn::with(relations: ['products', 'stockInvoice','supplier'])
            ->where('product_category_id', 3)
            ->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'invoice_id' => $stock->invoice_id,
                'product_id' => $stock->product_id,
                'date' => $stock->stockInvoice->date,
                'supplier' => $stock->stockInvoice->supplier->name,
                'lot_no' => $stock->lot_no,
                'invoice_no' => $stock->invoice_no,
                'length' => $stock->length,
                'width' => $stock->width,
                'length_unit' => $stock->length_unit,
                'width_unit' => $stock->width_unit,
                'pcs' => $stock->pcs,
                'quantity' => $stock->quantity,
                'out_quantity' => $stock->out_quantity,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return response()->json($stocks);
    }
    public function HoneyCombStocks()
    {
        $stocks = StocksIn::with(relations: ['products', 'stockInvoice','supplier'])
            ->where('product_category_id', 4)
            ->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'invoice_id' => $stock->invoice_id,
                'product_id' => $stock->product_id,
                'lot_no' => $stock->lot_no,
                'invoice_no' => $stock->invoice_no,
                'date' => $stock->stockInvoice->date,
                'supplier' => $stock->stockInvoice->supplier->name,
                'length' => $stock->length,
                'width' => $stock->width,
                'length_unit' => $stock->length_unit,
                'width_unit' => $stock->width_unit,
                'pcs' => $stock->pcs,
                'quantity' => $stock->quantity,
                'out_quantity' => $stock->out_quantity,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return response()->json($stocks);
    }
    public function store(StockInRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $createdItems = [];

            foreach ($validatedData as $data) {
                $data['user_id'] =  Auth::id();
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
        DB::beginTransaction();
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

                if (empty($row[1]) || empty($row[3])) {
                    DB::rollBack();
                    return response()->json(['error' => 'Required fields shadeNo or invoice_no are missing'], 422);
                }

                $shadeNo = $row[3];
                $invoiceNo = $row[1];
                $product = Product::where('shadeNo', $shadeNo)->first();
                $invoice = StockInvoice::where('invoice_no', $invoiceNo)->first();

                if (!$product) {
                    DB::rollBack();
                    return response()->json(['error' => "Product with shadeNo {$shadeNo} not found"], 422);
                }
                if (!$invoice) {
                    DB::rollBack();
                    return response()->json(['error' => "Invoice with invoice_no {$invoiceNo} not found"], 422);
                }
                $data = [
                    'product_category_id'=>$product->ProductCategory->id,
                    'product_id'  => $product->id,
                    'invoice_id'  => $invoice->id,
                    'user_id'     => Auth::id(),
                    'invoice_no'  => $invoiceNo,
                    'lot_no'      => $row[2] ?? null,
                    'width'       => $row[4] ?? null,
                    'width_unit'  => $row[5] ?? null,
                    'length'      => $row[6] ?? null,
                    'length_unit' => $row[7] ?? null,
                    'rack'        => $row[8] ?? null,
                    'pcs'         => $row[9] ?? 1,
                    'quantity'    => $row[10] ?? 1,
                ];

                $createdItem = StocksIn::create($data);
                $createdItems[] = $createdItem;
            }
            DB::commit();
            return $this->successResponse($createdItem, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process file', 'message' => $e->getMessage()], 500);
        }
    }
    public function CheckStocks($id)
    {
        $stocks = StocksIn::where('product_id', $id)
            ->where('status', 1)
            ->with(['products', 'products.ProductCategory'])->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($stock) {
            return [
                'stock_available_id' => $stock->id,
                'lot_no' => $stock->lot_no,
                'length' => $stock->length,
                'width' => $stock->width,
                'length_unit' => $stock->length_unit?? 'N/A',
                'width_unit' => $stock->width_unit?? 'N/A',
                'pcs' => $stock->pcs,
                'out_quantity' => $stock->quantity - $stock->out_quantity,
                'rack' => $stock->rack,
                'product_name' => $stock->products->name ?? 'N/A',
                'product_shadeNo' => $stock->products->shadeNo ?? 'N/A',
                'product_purchase_shade_no' => $stock->products->purchase_shade_no ?? 'N/A',
                'product_category' => $stock->products->ProductCategory->product_category ?? 'N/A',
            ];
        });

        return $this->successResponse($responseData, 'Active stocks retrieved successfully.', 200);
    }
    public function show($id)
    {
        $stocks = StocksIn::with(['products'])
            ->where('invoice_id', $id)
            ->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'invoice_id' => $stock->invoice_id,
                'product_id' => $stock->product_id,
                'lot_no' => $stock->lot_no,
                'invoice_no' => $stock->invoice_no,
                'length' => $stock->length,
                'width' => $stock->width,
                'length_unit' => $stock->length_unit,
                'width_unit' => $stock->width_unit,
                'type' => $stock->type,
                'pcs' => $stock->pcs,
                'quantity' => $stock->quantity,
                'out_quantity' => $stock->out_quantity,
                'rack' => $stock->rack,
                'warehouse' => $stock->warehouse,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
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
