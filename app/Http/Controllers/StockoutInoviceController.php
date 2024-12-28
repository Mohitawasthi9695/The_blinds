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
            'bank_id' => $validatedData['bank_id'] ?? 1,
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

        foreach ($validatedData['out_products'] as $product) {
            // Check if stock is available and meets the required conditions
            $availableStock = AvailableStock::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();
        
            if (!$availableStock) {
                return response()->json(['error' => 'Stock not available for the specified product requirements.'], 422);
            }
        
            if ($availableStock->qty < $product['out_quantity']) {
                return response()->json(['error' => 'Insufficient quantity available in stock.'], 422);
            }
        
            // Convert out_length and out_width to meters if needed
            $outLength = $product['out_length'];
            $outWidth = $product['out_width'];
        
            if ($product['unit'] === 'inches') {
                $outLength *= 0.0254; // Convert inches to meters
                $outWidth *= 0.0254;
            } elseif ($product['unit'] === 'feet') {
                $outLength *= 0.3048; // Convert feet to meters
                $outWidth *= 0.3048;
            }
        
            // Calculate sellable area (in square meters)
            $sellArea = $outLength * $outWidth * $product['out_quantity'];
        
            // Calculate actual out area (in square meters)
            $actualOutArea = $outLength * $availableStock->width * $product['out_quantity'];
        
            // Calculate waste area (in square meters)
            $wasteArea = $actualOutArea - $sellArea;
        
            // Update stock values
            $remainingArea = round($availableStock->area - $actualOutArea, 5);
            $remainingAreaSqFt = round($remainingArea * 10.764, 5); // Convert remaining area to square feet for reference
        
            $availableStock->update([
                'qty' => $availableStock->qty - $product['out_quantity'],
                'area' => $remainingArea,
                'area_sq_ft' => $remainingAreaSqFt,
                'status' => ($availableStock->qty - $product['out_quantity'] > 0) ? 1 : 0, // Active if stock remains
            ]);
        
            // Record stock-out details
            StockOutDetail::create([
                'stockout_inovices_id' => $stockOutInvoice->id,
                'stock_available_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'product_type' => $product['product_type'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'out_quantity' => $product['out_quantity'],
                'out_width' => round($outWidth, 5), 
                'out_length' => round($outLength, 5), 
                'unit' => $product['unit']?? null, 
                'area' => round($sellArea, 5),
                'area_sq_ft' => round($sellArea * 10.764, 5), // Convert to square feet for reference
                'waste_width' => round($availableStock->width - $outWidth, 5),
                'waste_area' => round($wasteArea, 5),
                'waste_area_sq_ft' => round($wasteArea * 10.764, 5), // Convert to square feet for reference
                'rate' => $product['rate'],
                'amount' => $product['amount'],
            ]);
        }
        

        // Return success response
        return $this->successResponse($stockOutInvoice, 'StockInvoice created successfully.', 201);
    }
}
