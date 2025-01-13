<?php

namespace App\Http\Controllers;

use App\Http\Requests\GodownStockOutRequest;
use App\Http\Requests\GodownStore;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use App\Models\StocksIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GodownController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = Godown::with('products')->get();
        return response()->json($stocks);
    }

    public function godownStock($id)
    {
        $stocks = Godown::where('status', '1')->where('godown_supervisor_id', $id)->with('products')->get();
        return response()->json($stocks);
    }


    public function GetStockProducts()
    {
        $products = Product::whereHas('godowns')->get();
        return response()->json($products);
    }
    public function GetStockCheckout($product_id)
    {
        $product = Product::find($product_id);
        if (!$product) {
            return $this->errorResponse('Product not found.', 404);
        }
        $stocks = $product->godowns()->where('status', 1)->with('products')->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($stock) {
            return [
                'stock_available_id' => $stock->id,
                'product_id' => $stock->product_id,
                'stock_code' => $stock->stock_code,
                'lot_no' => $stock->lot_no,
                'out_length' => $stock->available_height,
                'out_width' => $stock->get_width,
                'unit' => $stock->unit,
                'area_sq_ft' => round($stock->available_height * $stock->get_width * 10.7639,2),
                'area' => $stock->available_height * $stock->get_width,
                'product_type' => $stock->type,
                'out_quantity' => $stock->get_quantity,
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
    public function GodownStockOut(GodownStockOutRequest $request)
    {
        $validatedData = $request->validated();
        Log::info($validatedData);
        $stockOutInvoice = StockoutInovice::create([
            'invoice_no' => $validatedData['invoice_no'],
            'customer_id' => $validatedData['customer_id'],
            'date' => $validatedData['date'],
            'place_of_supply' => $validatedData['place_of_supply'],
            'vehicle_no' => $validatedData['vehicle_no'] ?? null,
            'station' => $validatedData['station'] ?? null,
            'ewaybill' => $validatedData['ewaybill'] ?? null,
            'reverse_charge' => $validatedData['reverse_charge'] ?? false,
            'gr_rr' => $validatedData['gr_rr'] ?? null,
            'transport' => $validatedData['transport'] ?? null,
            'receiver_id' => $validatedData['receiver_id'],
            'irn' => $validatedData['irn'] ?? null,
            'ack_no' => $validatedData['ack_no'] ?? null,
            'ack_date' => $validatedData['ack_date'] ?? null,
            'total_amount' => $validatedData['total_amount'],
            'cgst_percentage' => $validatedData['cgst_percentage'] ?? null,
            'sgst_percentage' => $validatedData['sgst_percentage'] ?? null,
            'payment_mode' => $validatedData['payment_mode'] ?? null,
            'payment_status' => $validatedData['payment_status'] ?? null,
            'payment_date' => $validatedData['payment_date'] ?? null,
            'payment_Bank' => $validatedData['payment_bank'] ?? null,
            'payment_account_no' => $validatedData['payment_account_no'] ?? null,
            'payment_ref_no' => $validatedData['payment_ref_no'] ?? null,
            'payment_amount' => $validatedData['payment_amount'] ?? null,
            'payment_remarks' => $validatedData['payment_remarks'] ?? null,
            'qr_code' => $validatedData['qr_code'] ?? null,
            'status' => 0,
        ]);

        foreach ($validatedData['out_products'] as $product) {
            $availableStock = Godown::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();

            if (!$availableStock) {
                return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
            }

            if ($availableStock->qty < $product['out_quantity']) {
                return response()->json(['error' => 'Insufficient quantity available in stock.'], 422);
            }

            $outLength = $product['out_length'];
            $outWidth = $product['out_width'];
            if ($product['unit'] === 'inches') {
                $outLength *= 0.0254;
                $outWidth *= 0.0254;
            } elseif ($product['unit'] === 'feet') {
                $outLength *= 0.3048;
                $outWidth *= 0.3048;
            }
            $restLength = $availableStock->length - $outLength;
            $restWidth = $availableStock->width - $outWidth;
            $create =  StockOutDetail::create([
                'stockout_inovice_id' => $stockOutInvoice->id,
                'stock_code' => $stockOutInvoice->stock_code,
                'stock_in_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'product_type' => $product['product_type'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'out_quantity' => 1 ?? null,
                'out_width' => round($outWidth, 5),
                'out_length' => round($outLength, 5),
                'unit' => $product['unit'] ?? null,
                'waste_width' => $restWidth,
                'rate' => $product['rate'],
                'amount' => $product['amount'],
                'status' => 0,
            ]);
        }
        return $this->successResponse($stockOutInvoice, 'StocksInvoice created successfully.', 201);
    }

    public function Sub_supervisorStock($id)
    {
        log::info($id);
        $stocks = Godown::where('godown_supervisor_id', $id)
            ->with('products') ->orderBy('id', 'desc')
            ->get();
        log::info($stocks);
        return response()->json($stocks);
    }

    public function invoice_no()
    {
        $lastInvoice = Godown::select('invoice_no')->orderBy('id', 'desc')->first();
        log::info($lastInvoice);
        if ($lastInvoice) {
            $lastInvoiceNo = $lastInvoice->invoice_no;
            $prefix = substr($lastInvoiceNo, 0, 2);
            $number = (int) substr($lastInvoiceNo, 2);
            $newNumber = $number + 1;
            $invoice_no = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $invoice_no = 'GT0001';
        }

        return $this->successResponse($invoice_no, 'Invoice number retrieved successfully.');
    }

    public function store(GodownStore $request)
    {
        $validatedData = $request->validated();
        Log::info($validatedData);

        foreach ($validatedData['out_products'] as $product) {
            $availableStock = StocksIn::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();

            if (!$availableStock || $availableStock->qty < $product['out_quantity']) {
                return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
            }

            $outLength = $product['out_length'];
            $outWidth = $product['out_width'];

            if ($product['unit'] === 'inches') {
                $outLength *= 0.0254;
                $outWidth *= 0.0254;
            } elseif ($product['unit'] === 'feet') {
                $outLength *= 0.3048; 
                $outWidth *= 0.3048;  
            }
            if ($outLength > $availableStock->available_height || $outWidth > $availableStock->available_width) {
                return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
            }
            $restWidth = $availableStock->available_width - $outWidth;
            $create = Godown::create([
                'invoice_no' => $validatedData['invoice_no'],
                'stock_in_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'date' => $validatedData['date'],
                'warehouse_supervisor_id' => $validatedData['warehouse_supervisor_id'],
                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
                'stock_code' => $product['stock_code'],
                'product_type' => $product['product_type'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'get_quantity' => $product['out_quantity'] ?? null,
                'get_width' => round($outWidth, 5),
                'get_length' => round($outLength, 5),
                'available_height' => round($outLength, 5),
                'available_width' => round($outWidth, 5),
                'unit' => $product['unit'] ?? null,
                'waste_width' => $restWidth,
                'status' => 0,
            ]);

            // Update the available stock after outing the stock out
            $remainingLength = $availableStock->available_height - $outLength;
            $remainingWidth = $availableStock->available_width - $outWidth;

            // If stock dimensions are exhausted, set qty to 0 and status to inactive (0)
            $newQty = ($remainingLength <= 0) ? 0 : $availableStock->qty;

            $availableStock->update([
                'available_height' => max($remainingLength, 0),
                'qty' => $newQty,
                'status' => ($remainingLength <= 0) ? 0 : 1,
            ]);
        }

        return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
    }


    public function GodownStockStatus(Request $request, $id)
    {
        $godown = Godown::find($id);
        $status = $request->status;
        if (!$godown) {
            return response()->json(['error' => 'Godown stock not found.'], 404);
        }
        if ($status == 2) {
            $availableStock = StocksIn::find($godown->stock_in_id);
            if (!$availableStock) {
                return response()->json(['error' => 'Stock-in record not found.'], 404);
            }
            $newAvailableHeight = $availableStock->available_height + $godown->get_length;
            $availableStock->update([
                'available_height' => $newAvailableHeight,
                'qty' => $availableStock->qty + $godown->get_quantity,
                'status' => 1,
            ]);
            $godown->status = $status;
            $godown->save();
            return response()->json(['success' => 'Godown stock has been rejected and stock has been restored to StockIn.'], 200);
        }
        $godown->status = $status;
        $godown->save();

        return response()->json(['success' => 'Godown stock status has been updated successfully.'], 200);
    }



    /**
     * Display the specified resource.
     */
    public function show(Godown $godown)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Godown $godown)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Godown $godown)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Godown $godown)
    {
        //
    }
}
