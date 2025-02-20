<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockOutRequest;
use App\Models\Godown;
use App\Models\GodownHoneyCombStock;
use App\Models\GodownRollerStock;
use App\Models\GodownVerticalStock;
use App\Models\GodownWoodenStock;
use App\Models\StocksIn;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockoutInoviceController extends ApiController
{

    public function index()
    {
        $stockOutInvoices = StockoutInovice::with([
            'stockOutDetails',
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
                        'rate' => round($detail->rate,3),
                        'amount' => round($detail->amount,3),
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
                    'product_shadeNo' => $detail->product->shadeNo ?? null,
                    'product_purchase_shade_no' => $detail->product->purchase_shade_no ?? null,
                    'product_category' => $detail->product->productCategory->product_category ?? null,
                ];
            }),
        ];

        return $this->successResponse($formattedData, 'StockOutInvoice retrieved successfully.');
    }
    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $StockoutInovice = StockoutInovice::where('id', $id)->first();
            log::info($StockoutInovice);
            if (!$StockoutInovice) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }
            $StockoutInovice->update(['status' => 1]);
            StockOutDetail::where('stockout_inovice_id', $StockoutInovice->id)->update(['status' => 1]);
            DB::commit();
            return response()->json(['success' => 'StockoutInovice approved successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to approve StockoutInovice.', 'message' => $e->getMessage()], 500);
        }
    }
    public function store(StockOutRequest $request)
    {
        DB::beginTransaction();
        try {

            $validatedData = $request->validated();
            log:
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
                if ($product['product_category_id'] === 1) {
                    $availableStock = GodownRollerStock::findorFail($product['godown_id']);
                    if ($product['length'] > $availableStock->length - $availableStock->out_length) {
                        DB::rollBack();
                        return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['godown_id']}.", 400);
                    }
                    $NewLength = $availableStock->length - ($availableStock->out_length + $product['length']);
                    $availableStock->update([
                        'out_length' => $availableStock->out_length + $product['length'],
                        'status' => ($NewLength <= 0) ? 0 : 1,
                        'quantity' => ($NewLength <= 0) ? 0 : 1,
                    ]);
                } elseif ($product['product_category_id'] === 2) {
                    $availableStock = GodownWoodenStock::findorFail($product['godown_id']);
                    if ($product['out_pcs'] > $availableStock->pcs - $availableStock->out_pcs) {
                        DB::rollBack();
                        return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                    }
                    $NewPcs = $availableStock->pcs - ($availableStock->out_pcs + $product['out_pcs']);
                    $availableStock->update([
                        'out_pcs' => $availableStock->out_pcs + $product['out_pcs'],
                        'status' => ($NewPcs <= 0) ? 0 : 1,
                        'quantity' => ($NewPcs <= 0) ? 0 : 1,
                    ]);
                } elseif ($product['product_category_id'] === 1) {
                    $availableStock = GodownVerticalStock::findorFail($product['godown_id']);
                    if ($product['length'] > $availableStock->length - $availableStock->out_length) {
                        DB::rollBack();
                        return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                    }
                    $NewLength = $availableStock->length - ($availableStock->out_length + $product['length']);
                    $availableStock->update([
                        'out_length' => $availableStock->out_length + $product['length'],
                        'status' => ($NewLength <= 0) ? 0 : 1,
                        'quantity' => ($NewLength <= 0) ? 0 : 1,
                    ]);
                } elseif ($product['product_category_id'] === 1) {
                    $availableStock = GodownHoneyCombStock::findorFail($product['godown_id']);
                    if ($product['out_pcs'] > $availableStock->pcs - $availableStock->out_pcs) {
                        DB::rollBack();
                        return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                    }
                    $NewPcs = $availableStock->pcs - ($availableStock->out_pcs + $product['out_pcs']);
                    $availableStock->update([
                        'out_pcs' => $availableStock->out_pcs + $product['out_pcs'],
                        'status' => ($NewPcs <= 0) ? 0 : 1,
                        'quantity' => ($NewPcs <= 0) ? 0 : 1,
                    ]);
                }
                if (!$availableStock) {
                    DB::rollBack();
                    return response()->json(['error' => 'Stock not available for the specified configuration.'], 422);
                }
                StockOutDetail::create([
                    'stockout_inovice_id' => $stockoutInvoice->id,
                    'godown_id' =>  $availableStock->id,
                    'stock_code' =>  $availableStock->stock_code,
                    'product_id' => $availableStock->product_id,
                    'date' => $product['date']?? null,
                    'out_width' => $product['width'],
                    'out_length' => $product['length'],
                    'width_unit' => $product['width_unit'] ?? null,
                    'length_unit' => $product['length_unit'] ?? null,
                    'out_pcs' => $product['out_pcs'] ?? 1,
                    'rate' => $product['rate'] ?? 1,
                    'amount' => $product['amount'] ?? 1,
                ]);
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

        DB::transaction(function () use ($stockOutInvoice) {
            foreach ($stockOutInvoice->stockOutDetails as $detail) {
                $availableStock = null;

                // Determine which stock table to update based on product_category_id
                if ($detail->product->product_category_id === 1) {
                    $availableStock = GodownRollerStock::find($detail->godown_id);
                    if ($availableStock) {
                        $availableStock->update([
                            'out_length' => max($availableStock->out_length - $detail->out_length, 0),
                            'status' => 1,
                            'quantity' => 1,
                        ]);
                    }
                } elseif ($detail->product->product_category_id === 2) {
                    $availableStock = GodownWoodenStock::find($detail->godown_id);
                    if ($availableStock) {
                        $availableStock->update([
                            'out_pcs' => max($availableStock->out_pcs - $detail->out_pcs, 0),
                            'status' => 1,
                            'quantity' => 1,
                        ]);
                    }
                } elseif ($detail->product->product_category_id === 3) {
                    $availableStock = GodownVerticalStock::find($detail->godown_id);
                    if ($availableStock) {
                        $availableStock->update([
                            'out_length' => max($availableStock->out_length - $detail->out_length, 0),
                            'status' => 1,
                            'quantity' => 1,
                        ]);
                    }
                } elseif ($detail->product->product_category_id === 4) {
                    $availableStock = GodownHoneyCombStock::find($detail->godown_id);
                    if ($availableStock) {
                        $availableStock->update([
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
