<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockOutRequest;
use App\Models\GodownHoneyCombStock;
use App\Models\GodownRollerStock;
use App\Models\GodownVerticalStock;
use App\Models\GodownWoodenStock;
use App\Models\Product;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOutController extends ApiController
{


    public function Sales()
    {
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();
        $startOfQuarter = now()->firstOfQuarter();
        $startOfYear = now()->startOfYear();
        $sumToday = StockOutDetail::where('created_at', '>=', $today)->sum('amount');
        $sumMonth = StockOutDetail::where('created_at', '>=', $startOfMonth)->sum('amount');
        $sumQuarter = StockOutDetail::where('created_at', '>=', $startOfQuarter)->sum('amount');
        $sumYear = StockOutDetail::where('created_at', '>=', $startOfYear)->sum('amount');
        $out_quantity = StockOutDetail::where('out_quantity', '>', 0)->sum('out_quantity');
        $today_out_quantity = StockOutDetail::where('created_at', '>=', $today)->sum('out_quantity');
        $data = [
            'totals' => [
                'sum_today' => round($sumToday, 2),
                'sum_month' => round($sumMonth, 2),
                'sum_quarter' => round($sumQuarter, 2),
                'sum_year' => round($sumYear, 2),
                'out_quantity' => round($out_quantity, 0),
                'today_out_quantity' => round($today_out_quantity, 0),
            ],
        ];
        return $this->successResponse($data, 'StockOutDetails and amounts retrieved successfully.');
    }

    public function StockOutDash(Request $request)
    {
        $filter = $request->query('filter', 'all');
        $stockOut = StockOutDetail::query();
        switch ($filter) {
            case 'today':
                $stockOut->whereDate('created_at', now()->toDateString());
                break;
            case 'this_week':
                $stockOut->whereBetween('created_at', [
                    now()->startOfWeek()->toDateTimeString(),
                    now()->endOfWeek()->toDateTimeString()
                ]);
                break;
            case 'all':
            default:
                break;
        }
        $stockOutDetails = $stockOut->get();
        return $this->successResponse($stockOutDetails, 'StockOutDetails retrieved successfully.');
    }

    public function GodownStockOutApprove(Request $request, $id)
    {
        $StockOutInvoice = StockoutInovice::find($id);
        if (!$StockOutInvoice) {
            return response()->json(['error' => 'Stockout invoice not found.'], 404);
        }
        $validatedData = $request->validate([
            'status' => 'required|integer',
        ]);
        $StockOutInvoice->status = $validatedData['status'];
        $StockOutInvoice->save();
        $StockOutInvoice->stockOutDetails()->update(['status' => $validatedData['status']]);
        return response()->json([
            'message' => 'Stockout invoice and related stock out details updated successfully.',
        ], 200);
    }

    public function CheckStocks($id)
    {
        $Product = Product::findorFail($id);
        if ($Product->product_category_id === 1) {
            $stocks = GodownRollerStock::where('product_id', $id)
                ->where('status', 1)
                ->with(['products', 'products.ProductCategory'])->get();
        } else if ($Product->product_category_id === 2) {
            $stocks = GodownWoodenStock::where('product_id', $id)
                ->where('status', 1)
                ->with(['products', 'products.ProductCategory'])->get();
        } else if ($Product->product_category_id === 3) {
            $stocks = GodownVerticalStock::where('product_id', $id)
                ->where('status', 1)
                ->with(['products', 'products.ProductCategory'])->get();
        } else if ($Product->product_category_id === 4) {
            $stocks = GodownHoneyCombStock::where('product_id', $id)
                ->where('status', 1)
                ->with(['products', 'products.ProductCategory'])->get();
        }


        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($stock) {
            return [
                'godown_id' => $stock->id,
                'product_category_id' => $stock->products->ProductCategory->id,
                'lot_no' => $stock->lot_no,
                'stock_code' => $stock->stock_code,
                'length' => $stock->length - ($stock->out_length ?? 0) ?? 1,
                'width' => $stock->width,
                'length_unit' => $stock->length_unit ?? 'N/A',
                'width_unit' => $stock->width_unit ?? 'N/A',
                'out_pcs' => ($stock->pcs - $stock->out_pcs) ?? 1,
                'rack' => $stock->rack ?? 'N/A',
                'product_name' => $stock->products->name ?? 'N/A',
                'product_shadeNo' => $stock->products->shadeNo ?? 'N/A',
                'product_purchase_shade_no' => $stock->products->purchase_shade_no ?? 'N/A',
                'product_category' => $stock->products->ProductCategory->product_category ?? 'N/A',
            ];
        });

        return $this->successResponse($responseData, 'Active stocks retrieved successfully.', 200);
    }

    public function GodownStockOut(StockOutRequest $request)
    {
        DB::beginTransaction();
        try {

            $validatedData = $request->validated();
            log:info($validatedData);
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
                'status' => $validatedData['status'] ?? 1,
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
                    'out_width' => round($product['width'], 2),
                    'out_length' => round($product['length'], 2),
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
}
