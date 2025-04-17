<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockOutRequest;
use App\Models\CutStock;
use App\Models\GodownAccessory;
use App\Models\GodownHoneyCombStock;
use App\Models\GodownRollerStock;
use App\Models\GodownVerticalStock;
use App\Models\GodownWoodenStock;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UnitHelper;

class StockoutInoviceController extends ApiController
{
    public function index()
    {
        $stockOutInvoices = StockoutInovice::with([
            'stockOutDetails.product',
            'stockOutDetails.product.productCategory',
            'customer',
            'company'
        ])->get();
        $formattedData = $stockOutInvoices->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'date' => $invoice->date,
                'customer' => $invoice->customer->name ?? null,
                'company' => $invoice->company->name ?? null,
                'place_of_supply' => $invoice->place_of_supply,
                'vehicle_no' => $invoice->vehicle_no,
                'station' => $invoice->station,
                'ewaybill' => $invoice->ewaybill,
                'reverse_charge' => $invoice->reverse_charge,
                'gr_rr' => $invoice->gr_rr,
                'transport' => $invoice->transport,
                'irn' => $invoice->irn,
                'ack_no' => $invoice->ack_no,
                'ack_date' => $invoice->ack_date,
                'total_amount' => $invoice->total_amount,
                'cgst_percentage' => $invoice->cgst_percentage,
                'sgst_percentage' => $invoice->sgst_percentage,
                'payment_mode' => $invoice->payment_mode,
                'payment_status' => $invoice->payment_status,
                'payment_date' => $invoice->payment_date,
                'payment_bank' => $invoice->payment_bank,
                'payment_account_no' => $invoice->payment_account_no,
                'payment_ref_no' => $invoice->payment_ref_no,
                'payment_amount' => $invoice->payment_amount,
                'payment_remarks' => $invoice->payment_remarks,
                'qr_code' => $invoice->qr_code,
                'status' => $invoice->status,
                'stock_out_details' => $invoice->stockOutDetails->map(function ($detail) use ($invoice) {
                    return [
                        'id' => $detail->id,
                        'stockout_invoice_id' => $detail->stockout_inovice_id,
                        'godown_id' => $detail->godown_id,
                        'product_id' => $detail->product_id,
                        'stock_code' => $detail->stock_code,
                        'out_width' => round($detail->out_width, 2),
                        'out_length' => round($detail->out_length, 2),
                        'out_pcs' => round($detail->out_pcs),
                        'width_unit' => $detail->width_unit,
                        'length_unit' => $detail->length_unit,
                        'type' => $detail->type,
                        'gst' => $detail->gst,
                        'rate' => round($detail->rate, 3),
                        'amount' => round($detail->amount, 3),
                        'rack' => $detail->rack,
                        'status' => $detail->status,
                        'product_name' => $detail->product->name ?? null,
                        'product_shadeNo' => $detail->product->shadeNo ?? null,
                        'product_purchase_shade_no' => $detail->product->purchase_shade_no ?? null,
                        'product_category' => $detail->product->productCategory->product_category ?? null,
                    ];
                }),
            ];
        });

        return $this->successResponse($formattedData, 'StockOutInvoices retrieved successfully.');
    }
    public function invoice_no()
    {
        $lastInvoice = StockoutInovice::select('invoice_no')->orderBy('id', 'desc')->first();

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
        $stockOutInvoice = StockoutInovice::with([
            'stockOutDetails',
            'stockOutDetails.product',
            'stockOutDetails.product.productCategory',
            'stockOutDetails.godown.gatepass.godown_supervisors',
            'customer',
            'company'
        ])->find($id);

        if (!$stockOutInvoice) {
            return $this->errorResponse('StockOutInvoice not found.', 404);
        }

        $formattedData = [
            'id' => $stockOutInvoice->id,
            'invoice_no' => $stockOutInvoice->invoice_no,
            'date' => $stockOutInvoice->date,
            'customer' => $stockOutInvoice->customer->name ?? null,
            'company' => $stockOutInvoice->company->name ?? null,
            'place_of_supply' => $stockOutInvoice->place_of_supply,
            'vehicle_no' => $stockOutInvoice->vehicle_no,
            'station' => $stockOutInvoice->station,
            'ewaybill' => $stockOutInvoice->ewaybill,
            'reverse_charge' => $stockOutInvoice->reverse_charge,
            'gr_rr' => $stockOutInvoice->gr_rr,
            'transport' => $stockOutInvoice->transport,
            'irn' => $stockOutInvoice->irn,
            'ack_no' => $stockOutInvoice->ack_no,
            'ack_date' => $stockOutInvoice->ack_date,
            'total_amount' => $stockOutInvoice->total_amount,
            'cgst_percentage' => $stockOutInvoice->cgst_percentage,
            'sgst_percentage' => $stockOutInvoice->sgst_percentage,
            'payment_mode' => $stockOutInvoice->payment_mode,
            'payment_status' => $stockOutInvoice->payment_status,
            'payment_date' => $stockOutInvoice->payment_date,
            'payment_Bank' => $stockOutInvoice->payment_Bank,
            'payment_account_no' => $stockOutInvoice->payment_account_no,
            'payment_ref_no' => $stockOutInvoice->payment_ref_no,
            'payment_amount' => $stockOutInvoice->payment_amount,
            'payment_remarks' => $stockOutInvoice->payment_remarks,
            'qr_code' => $stockOutInvoice->qr_code,
            'status' => $stockOutInvoice->status,
            'stock_out_details' => $stockOutInvoice->stockOutDetails->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'stockout_invoice_id' => $detail->stockout_inovice_id,
                    'godown_id' => $detail->godown_id,
                    'product_id' => $detail->product_id,
                    'stock_code' => $detail->stock_code,
                    'width' => round($detail->out_width, 2),
                    'length' => round($detail->out_length, 2),
                    'out_pcs' => $detail->out_pcs,
                    'width_unit' => $detail->width_unit,
                    'length_unit' => $detail->length_unit,
                    'type' => $detail->type,
                    'gst' => $detail->gst,
                    'rate' => $detail->rate,
                    'amount' => $detail->amount,
                    'rack' => $detail->rack,
                    'status' => $detail->status,
                    'product_name' => $detail->product->name ?? null,
                    'godown_supervisor' => $detail->godown->gatepass->godown_supervisors->name ?? null,
                    'product_shadeNo' => $detail->product->shadeNo ?? null,
                    'product_purchase_shade_no' => $detail->product->purchase_shade_no ?? null,
                    'product_category' => $detail->product->productCategory->product_category ?? null,
                ];
            }),
        ];

        return $this->successResponse($formattedData, 'StockOutInvoice retrieved successfully.');
    }
    public function store(StockOutRequest $request)
    {

        DB::beginTransaction();
        try {

            $validatedData = $request->validated();

            info($validatedData);
            $stockoutInvoice = StockoutInovice::create([
                'invoice_no' => $validatedData['invoice_no'],
                'date' => $validatedData['date'],
                'customer_id' => $validatedData['customer_id'],
                'company_id' => $validatedData['company_id'] ?? null,
                'place_of_supply' => $validatedData['place_of_supply'] ?? null,
                'vehicle_no' => $validatedData['vehicle_no'] ?? null,
                'station' => $validatedData['station'] ?? null,
                'ewaybill' => $validatedData['ewaybill'] ?? null,
                'reverse_charge' => $validatedData['reverse_charge'] ?? 0,
                'gr_rr' => $validatedData['gr_rr'] ?? null,
                'transport' => $validatedData['transport'] ?? null,
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
                'payment_amount' => $validatedData['payment_amount'] ?? 0,
                'payment_remarks' => $validatedData['payment_remarks'] ?? null,
                'qr_code' => $validatedData['qr_code'] ?? null,
            ]);

            foreach ($validatedData['out_products'] as $product) {
                if ($product) {
                    $availableStock = GodownRollerStock::findOrFail($product['godown_id']);
                    if (!$availableStock) {
                        return $this->errorResponse("Something went wrong, godown not found", 400);
                    }
                    if ($product['type'] == 1) {
                        $sellLength = convertUnit($product['length'], $product['length_unit'], $availableStock->length_unit, 2);
                        $sellWidth = convertUnit($product['width'], $product['width_unit'], $availableStock->width_unit, 2);
                        $CutStock = CutStock::where('godown_roller_stock_id', $availableStock->id)
                            ->whereRaw('(length - out_length) >= ?', [$sellLength])
                            ->where('width', '>=', $sellWidth)
                            ->first();
                        if ($CutStock) {
                            log::info("cut cal");
                            $AvlLength = $CutStock->length - ($CutStock->out_length + $sellLength);
                            $cutWidth = $CutStock->width - $sellWidth;
                        } else {
                            log::info("main cal");
                            if ($sellLength > ($availableStock->length - $availableStock->out_length)) {
                                DB::rollBack();
                                return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['godown_id']}.", 400);
                            }
                            if ($sellWidth > $availableStock->width) {
                                DB::rollBack();
                                return $this->errorResponse("Insufficient sellWidth available for Stock-in ID {$product['godown_id']}.", 400);
                            }
                            $AvlLength = $availableStock->length - ($availableStock->out_length + $sellLength);
                            $cutWidth = $availableStock->width - $sellWidth;
                        }
                        if ($cutWidth > 0.2 && $sellLength > 1) {
                            CutStock::create([
                                'godown_roller_stock_id' => $availableStock->id,
                                'stockout_inovice_id' => $stockoutInvoice->id,
                                'length' => $sellLength,
                                'out_length' => 0,
                                'length_unit' => $availableStock->length_unit,
                                'width' => $cutWidth,
                                'width_unit' => $availableStock->width_unit,
                                'status' => 1,
                            ]);
                            $wastage = 0;
                        } else {
                            $wastage = $sellLength * $cutWidth;
                        }
            
                        // Update CutStock if used
                        if ($CutStock) {
                            $CutStock->update([
                                'out_length' => $CutStock->out_length + $sellLength,
                                'wastage' => max(($CutStock->wastage + $wastage), 0),
                                'status' => ($AvlLength <= 0) ? 2 : 1,
                            ]);
                        } else {
                            // Update the main stock if no cut stock was used
                            $availableStock->update([
                                'out_length' => $availableStock->out_length + $sellLength,
                                'out_pcs' => (($availableStock->out_length + $sellLength) <= 0) ? $product['out_pcs'] : 0,
                                'wastage' => max(($availableStock->wastage + $wastage), 0),
                                'status' => ($AvlLength <= 0) ? 2 : 1,
                                'quantity' => ($AvlLength <= 0) ? 2 : 1,
                            ]);
                        }
                    }
            
                    if ($product['type'] == 0) {
                        if ($product['out_pcs'] > $availableStock->pcs - $availableStock->out_pcs) {
                            DB::rollBack();
                            return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['godown_id']}.", 400);
                        }
            
                        $Avlpcs = $availableStock->pcs - ($availableStock->out_pcs + $product['out_pcs']);
                        $wastage = 0;
            
                        $availableStock->update([
                            'out_pcs' => $availableStock->out_pcs + $product['out_pcs'],
                            'wastage' => max(($availableStock->wastage + $wastage), 0),
                            'status' => ($Avlpcs <= 0) ? 2 : 1,
                            'quantity' => ($Avlpcs <= 0) ? 2 : 1,
                        ]);
                    }
            
                    if (!$availableStock) {
                        DB::rollBack();
                        return response()->json(['error' => 'Stock not available for the specified configuration.'], 422);
                    }
            
                    // Create StockOut Detail
                    StockOutDetail::create([
                        'stockout_inovice_id' => $stockoutInvoice->id,
                        'godown_id' => $availableStock->id,
                        'stock_code' => isset($CutStock) ? $availableStock->stock_code . '-' . $CutStock->sub_stock_code : $availableStock->stock_code,
                        'product_id' => $availableStock->product_id,
                        'date' => $validatedData['date'] ?? null,
                        'out_width' => $product['width'],
                        'out_length' => $product['length'],
                        'width_unit' => $product['width_unit'] ?? null,
                        'length_unit' => $product['length_unit'] ?? null,
                        'out_pcs' => $product['out_pcs'] ?? 1,
                        'rate' => $product['rate'] ?? 1,
                        'gst' => $product['gst'] ?? 0,
                        'rack' => $availableStock->rack ?? 'N/A',
                        'amount' => $product['amount'] ?? 1,
                    ]);
                }
            }
            DB::commit();
            return response()->json(['success' => 'StockOut has been successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to Add Gate Pass => ' . $e->getMessage(), 500);
        }
    }
    public function destroy($id)
    {
        $stockOutInvoice = StockoutInovice::find($id);

        if (!$stockOutInvoice) {
            return $this->errorResponse('StockOutInvoice not found.', 404);
        }
        if ($stockOutInvoice->status === 2) {
            return $this->errorResponse('StockOutInvoice Approved No able to Delete.', 404);
        }
        DB::transaction(function () use ($stockOutInvoice) {
            foreach ($stockOutInvoice->stockOutDetails as $detail) {

                foreach ($detail->accessoryoutstock as $outstock) {
                    $availableStock = GodownAccessory::find($outstock->godown_accessory_id);
                    if ($availableStock){
                        $availableStock->update([
                            'out_quantity' => max($availableStock->out_quantity - $outstock->quantity, 0),
                            'status' => 1,
                        ]);
                    }
                }
                
                $availableStock = null;

                // Determine which stock table to update based on product_category_id
                if ($detail) {
                    $availableStock = GodownRollerStock::find($detail->godown_id);
                    if ($availableStock) {
                        $availableStock->update([
                            'out_length' => max($availableStock->out_length - $detail->out_length, 0),
                            'out_pcs' => max($availableStock->out_pcs - $detail->out_pcs, 0),
                            'status' => 1,
                            'quantity' => 1,
                        ]);
                    }
                }
                Log::info("Stock updated: ", $availableStock ? $availableStock->toArray() : []);
                $detail->delete();
            }
            $stockOutInvoice->delete();
        });

        return $this->successResponse(null, 'StockOutInvoice and associated records deleted successfully.');
    }

}
