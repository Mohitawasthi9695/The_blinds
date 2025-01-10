<?php

namespace App\Http\Controllers;

use App\Models\Godown;
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
        //
    }
    public function invoice_no()
    {
        $lastInvoice = Godown::where('status', '1')->select('invoice_no')->orderBy('id', 'desc')->first();

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
    



    public function store(Request $request)
    {
        $validatedData = $request->validated();
        Log::info($validatedData);
        
        foreach ($validatedData['out_products'] as $product) {
            // Fetch the available stock from StockIn
            $availableStock = StocksIn::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();
    
            // Check if stock exists and if there's sufficient quantity
            if (!$availableStock || $availableStock->qty < $product['send_quantity']) {
                return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
            }
    
            // Convert length and width based on the selected unit
            $outLength = $product['send_length'];
            $outWidth = $product['send_width'];
    
            if ($product['unit'] === 'inches') {
                $outLength *= 0.0254; // Convert inches to meters
                $outWidth *= 0.0254;  // Convert inches to meters
            } elseif ($product['unit'] === 'feet') {
                $outLength *= 0.3048; // Convert feet to meters
                $outWidth *= 0.3048;  // Convert feet to meters
            }
    
            // Ensure the dimensions do not exceed the available stock's dimensions
            if ($outLength > $availableStock->available_height || $outWidth > $availableStock->available_width) {
                return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
            }
    
            // Calculate the remaining width after sending the stock
            $restWidth = $availableStock->available_width - $outWidth;
    
            // Insert the record into the Godown table
            $create = Godown::create([
                'invoice_no' => $validatedData['invoice_no'],
                'stock_in_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'warehouse_supervisor_id' => $product['warehouse_supervisor_id'],
                'godown_supervisor_id' => $product['godown_supervisor_id'],
                'stock_code' => $product['stock_code'],
                'product_type' => $product['product_type'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'out_quantity' => $product['send_quantity'] ?? null,
                'out_width' => round($outWidth, 5),
                'out_length' => round($outLength, 5),
                'unit' => $product['unit'] ?? null,
                'waste_width' => $restWidth,
                'rate' => $product['rate'],
                'amount' => $product['amount'],
                'status' => 0, // Assuming 0 means "out" or "sent"
            ]);
    
            // Update the available stock after sending the stock out
            $remainingLength = $availableStock->available_height - $outLength;
            $remainingWidth = $availableStock->available_width - $outWidth;
    
            // If stock dimensions are exhausted, set qty to 0 and status to inactive (0)
            $newQty = ($remainingLength <= 0 ) ? 0 : $availableStock->qty;
    
            $availableStock->update([
                'available_height' => max($remainingLength, 0),
                'qty' => $newQty,  
                'status' => ($remainingLength <= 0) ? 0 : 1,
            ]);
        }
    
        return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
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
