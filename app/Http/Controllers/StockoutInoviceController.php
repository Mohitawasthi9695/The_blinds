<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockOutRequest;
use App\Models\Godown;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockoutInoviceController extends ApiController
{

    public function index()
    {
        $stockOutInvoices = StockoutInovice::with('customer', 'receiver', 'stockOutDetails.product')->get();
        return $this->successResponse($stockOutInvoices, 'StockOutInovices retrieved successfully.');
    }
    public function AllStockOut()
    {
        $stockOutInvoices = StockoutInovice::select('id', 'invoice_no', 'date')
            ->with('stockOutDetails.product')
            ->get();
        return $this->successResponse($stockOutInvoices, 'StockOutInvoices retrieved successfully.');
    }

    

    public function store(StockOutRequest $request)
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
    public function invoice_no()
    {
        $lastInvoice = StockoutInovice::where('status', '1')->select('invoice_no')->orderBy('id', 'desc')->first();

        if ($lastInvoice) {
            $lastInvoiceNo = $lastInvoice->invoice_no;
            $prefix = substr($lastInvoiceNo, 0, 2);
            $number = (int) substr($lastInvoiceNo, 2);
            $newNumber = $number + 1;
            $invoice_no = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $invoice_no = 'IN0001';
        }

        return $this->successResponse($invoice_no, 'Invoice number retrieved successfully.');
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
        log::info($stockOutInvoice);
        if (!$stockOutInvoice) {
            return $this->errorResponse('StockOutInvoice not found.', 404);
        }
        DB::transaction(function () use ($stockOutInvoice) {
            foreach ($stockOutInvoice->stockOutDetails as $detail) {
                log::info($detail);
                $availableStock = Godown::find($detail->godown_id);
                if ($availableStock) {
                    log::info($detail->out_length);
                    log::info($detail->out_width);
                    $availableStock->update([
                        'available_height' => $availableStock->available_height + $detail->get_length,
                        'available_width' => $availableStock->available_width + $detail->get_width,
                        'status' => 1,
                    ]);
                }
                $detail->delete();
            }
            $stockOutInvoice->delete();
        });
        return $this->successResponse(null, 'StockOutInvoice and associated records deleted successfully.');
    }
}
