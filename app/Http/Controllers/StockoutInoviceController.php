<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockOutRequest;
use App\Models\AvailableStock;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use Illuminate\Http\Request;

class StockoutInoviceController extends ApiController
{
   
    public function index()
    {
        //
    }
    public function create()
    {
        //
    }
    public function store(StockOutRequest $request)
    {
        $validatedData = $request->validated();

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
            'payment_bank' => $validatedData['payment_bank'] ?? null,
            'payment_account_no' => $validatedData['payment_account_no'] ?? null,
            'payment_ref_no' => $validatedData['payment_ref_no'] ?? null,
            'payment_amount' => $validatedData['payment_amount'] ?? null,
            'payment_remarks' => $validatedData['payment_remarks'] ?? null,
            'qr_code' => $validatedData['qr_code'] ?? null,

        ]);

        foreach ($validatedData['products'] as $product) {
            $availableStock = AvailableStock::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();
            if (!$availableStock) {
                return response()->json(['error' => 'Stock not available for the specified product requirements.'], 422);
            }
            if ($availableStock->qty < $product['out_quantity']) {
                return response()->json(['error' => 'Insufficient quantity available in stock.'], 422);
            }
            $sellArea = 0;
            $wasteArea = 0;

            if (isset($product['out_length']) && isset($product['out_width'])) {
                $sellArea = $product['out_length'] * $product['out_width'] * $product['out_quantity'];
                if ($product['unit'] === 'meter') {
                    $sellArea *= 10.764; 
                } elseif ($product['unit'] === 'inches') {
                    $sellArea /= 144; 
                }
            }

            $remainingAreaSqFt = $availableStock->area_sq_ft - $sellArea;
            $wasteArea = max(0, $remainingAreaSqFt);

            // Update the available stock
            $availableStock->update([
                'qty' => $availableStock->qty - $product['out_quantity'],
                'area' => max(0, $availableStock->area - $sellArea),
                'area_sq_ft' => $wasteArea,
                'status' => ($availableStock->qty > 0) ? 'Available' : 'Depleted',
            ]);

            // Insert the stock out record
            StockOutDetail::create([
                'stockout_inovices_id' => $stockOutInvoice->id,
                'stock_available_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'product_type' => $product['product_type'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'out_quantity' => $product['out_quantity'],
                'out_width' => $product['out_width'] ?? null,
                'out_length' => $product['out_length'] ?? null,
                'unit' => $product['unit'] ?? null,
                'area' => $sellArea,
                'area_sq_ft' => $sellArea,
                'rate' => $product['rate'],
                'amount' => $product['amount'],
            ]);

            // Log wastage if any
            if ($wasteArea > 0) {
                AvailableStock::create([
                    'stock_ins_id' => $availableStock->stock_ins_id,
                    'product_id' => $product['product_id'],
                    'length' => $availableStock->length,
                    'width' => $availableStock->width,
                    'unit' => $availableStock->unit,
                    'waste_area_sq_ft	' => $remainingAreaSqFt,
                    'area' => $wasteArea,
                    'qty' => 0,
                    'rack' => $availableStock->rack,
                    'status' => 'Wastage',
                ]);
            }
        }


        // Return success response
        return $this->successResponse($stockOutInvoice, 'StockInvoice created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(StockOutRequest $stockoutInovice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockOutRequest $StockOutRequest)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StockOutRequest $StockOutRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StockOutRequest $StockOutRequest)
    {
        //
    }
}
