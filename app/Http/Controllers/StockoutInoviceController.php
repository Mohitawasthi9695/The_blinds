<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockOutRequest;
use App\Models\AvailableStock;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;

class StockoutInoviceController extends ApiController
{

    public function index()
    {
        $stockOutInvoices = StockoutInovice::with('customer', 'receiver', 'stockOutDetails')->get();
        return $this->successResponse($stockOutInvoices, 'StockOutInovices retrieved successfully.');
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

        foreach ($validatedData['out_products'] as $product) {
            $availableStock = AvailableStock::where('id', $product['stock_available_id'])
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
            if ($restLength > 0 && $restWidth > 0) {
                $newStock = AvailableStock::where('product_id', $product['product_id'])
                    ->where('length', $restLength)
                    ->where('width', $availableStock->width)
                    ->where('unit', $availableStock->unit)
                    ->first();
        
                if (!$newStock) {
                    AvailableStock::create([
                        'product_id' => $product['product_id'],
                        'length' => $restLength,
                        'width' => $availableStock->width,
                        'unit' => $availableStock->unit,
                        'qty' => $product['out_quantity'],
                        'status' => 1,
                    ]);
                } else {
                    $newStock->update([
                        'qty' => $newStock->qty + $product['out_quantity'],
                    ]);
                }
            }
           
            StockOutDetail::create([
                'stockout_inovice_id' => $stockOutInvoice->id,
                'stock_available_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'product_type' => $product['product_type'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'out_quantity' => $product['out_quantity'],
                'out_width' => round($outWidth, 5),
                'out_length' => round($outLength, 5),
                'unit' => $product['unit'] ?? null,
                'waste_width' => $restWidth,
                'waste_area' =>$outLength,
                'rate' => $product['rate'],
                'amount' => $product['amount'],
            ]);
            $availableStock->update([
                'qty' => $availableStock->qty - $product['out_quantity'],
            ]);
            if ($availableStock->qty == 0) {
                $availableStock->delete();
            }
        }
        return $this->successResponse($stockOutInvoice, 'StockInvoice created successfully.', 201);
    }
    public function show($id)
    {
        $stockOutInvoice = StockoutInovice::with('customer', 'receiver', 'stockOutDetails')->find($id);
        if (!$stockOutInvoice) {
            return $this->errorResponse('StockOutInvoice not found.', 404);
        }
        return $this->successResponse($stockOutInvoice, 'StockOutInvoice retrieved successfully.');
    }


    public function destroy($id)
    {
        $stockOutInvoice = StockoutInovice::find($id);

        if (!$stockOutInvoice) {
            return $this->errorResponse('StockOutInvoice not found.', 404);
        }

        foreach ($stockOutInvoice->stockOutDetails as $detail) {
            $availableStock = AvailableStock::find($detail->stock_available_id);
            if ($availableStock) {
                $availableStock->update([
                    'qty' => $availableStock->qty + $detail->out_quantity,
                    'area' => $availableStock->area + $detail->area,
                    'area_sq_ft' => $availableStock->area_sq_ft + $detail->area_sq_ft,
                    'status' => 1,
                ]);
            }
            $detail->delete();
        }

        $stockOutInvoice->delete();

        return $this->successResponse(null, 'StockOutInvoice deleted successfully.');
    }
}
