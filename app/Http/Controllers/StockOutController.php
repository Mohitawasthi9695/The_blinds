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
    public function AllStockOut()
    {
        $stockOutInvoices = StockOutDetail::with(['stockOutInvoice', 'product','product.ProductCategory'])->get();

        if ($stockOutInvoices->isEmpty()) {
            return $this->errorResponse('No stock-out invoices found.', 404);
        }

        // Formatting response
        $formattedData = $stockOutInvoices->map(function ($item) {
            return [
                'stock_code' => $item->stock_code,
                'lot_no' => $item->lot_no,
                'product_name' => $item->product->name ?? null,
                'product_category' => $item->product->ProductCategory->product_category ?? null,
                'product_shade_no' => $item->product->shadeNo ?? null,
                'product_pur_shade_no' => $item->product->purchase_shade_no ?? null,
                'invoice_no' => $item->stockOutInvoice->invoice_no ?? null,
                'length' =>round($item->out_length,2) ?? 0,
                'width' => round($item->out_width,2)??0,
                'date' => $item->date??0,
                'hsn_sac_code ' => $item->hsn_sac_code ??0,
                'pcs' => round($item->out_pcs)??0,
                'gst' => $item->gst??0,
                'rate' => round($item->rate,2)??0,
                'amount' => round($item->amount,2)??0,
                'length_unit' => $item->length_unit ?? 'N/A',
                'width_unit' => $item->width_unit ?? 'N/A',
                'rack' => $item->rack ?? 'N/A',
            ];
        });

        return $this->successResponse($formattedData, 'StockOutInvoices retrieved successfully.');
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
                ->where('status', 1)->where('type','stock')
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
}
