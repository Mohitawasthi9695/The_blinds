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
use Illuminate\Support\Facades\Log;

class StockOutController extends ApiController
{
    public function index()
    {
        $user = Auth::user();
        $stocks = StockOutDetail::with([
            'stockOutInvoice',
            'product',
            'product.productCategory',
        ]);
        $stocks->whereHas('Godown', function ($query) use ($user) {
            $query->whereHas('gatepass', function ($query) use ($user) {
                $query->where('godown_supervisor_id', $user->id);
            });
        });
        $Stockout = $stocks->get();

        $formattedData = $Stockout->map(function ($detail) {
            return [
                'id' => $detail->id,
                'stockout_invoice_id' => $detail->stockout_inovice_id,
                'stockout_invoice_no' => $detail->stockOutInvoice->invoice_no,
                'godown_id' => $detail->godown_id,
                'product_id' => $detail->product_id,
                'stock_code' => $detail->stock_code,
                'out_width' => round($detail->out_width, 2),
                'out_length' => round($detail->out_length, 2),
                'out_pcs' => round($detail->out_pcs),
                'width_unit' => $detail->width_unit,
                'length_unit' => $detail->length_unit,
                'type' => $detail->type,
                'date' => $detail->date,
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
        });
        return $this->successResponse($formattedData, 'StockOutInvoices retrieved successfully.');
    }
    public function AllStockOut()
    {
        $stockOutInvoices = StockOutDetail::with(['stockOutInvoice', 'product', 'product.ProductCategory'])->get();

        if ($stockOutInvoices->isEmpty()) {
            return $this->errorResponse('No stock-out invoices found.', 404);
        }
        $formattedData = $stockOutInvoices->map(function ($item) {
            return [
                'id' => $item->id,
                'company_name' => $item->stockOutInvoice->company->name ?? null,
                'company_phone_no' => $item->stockOutInvoice->company->owner_mobile ?? null,
                'buyer_name' => $item->stockOutInvoice->customer->name ?? null,
                'buyer_phone_no' => $item->stockOutInvoice->customer->owner_mobile ?? null,
                'stock_code' => $item->stock_code,
                'lot_no' => $item->lot_no,
                'product_name' => $item->product->name ?? null,
                'product_category' => $item->product->ProductCategory->product_category ?? null,
                'product_shade_no' => $item->product->shadeNo ?? null,
                'product_pur_shade_no' => $item->product->purchase_shade_no ?? null,
                'invoice_no' => $item->stockOutInvoice->invoice_no ?? null,
                'length' => round($item->out_length, 2) ?? 0,
                'width' => round($item->out_width, 2) ?? 0,
                'date' => $item->date ?? 0,
                'hsn_sac_code ' => $item->hsn_sac_code ?? 0,
                'pcs' => round($item->out_pcs) ?? 0,
                'gst' => $item->gst ?? 0,
                'rate' => round($item->rate, 2) ?? 0,
                'amount' => round($item->amount, 2) ?? 0,
                'length_unit' => $item->length_unit ?? 'N/A',
                'width_unit' => $item->width_unit ?? 'N/A',
                'rack' => $item->rack ?? 'N/A',
                'status' => $item->status,
            ];
        });

        return $this->successResponse($formattedData, 'StockOutInvoices retrieved successfully.');
    }
    public function StockOutCustomer(Request $request)
    {
        $filter = $request->query('filter', 'all');

        $stockOut = StockOutDetail::with('stockOutInvoice.customer'); // Eager load
        switch ($filter) {
            case 'today':
                $stockOut->whereDate('created_at', now()->toDateString());
                break;
            case 'this_week':
                $stockOut->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ]);
                break;
            case 'all':
            default:
                break;
        }
        $stockOutDetails = $stockOut->get();
        $customers = $stockOutDetails
            ->pluck('stockOutInvoice.customer')
            ->filter()
            ->unique('id')
            ->filter(function ($customer) {
                return $customer->status == 1; 
            })
            ->values();
            Log::info($customers);
        return $this->successResponse($customers, 'Active customers retrieved successfully.');
    }
    public function StockOutProductRating()
    {
        $productStockData = StockOutDetail::with('product')
            ->select('product_id', DB::raw('COUNT(*) as stock_count'))
            ->groupBy('product_id')
            ->orderByDesc('stock_count')
            ->get();
        $max = $productStockData->max('stock_count');
        $min = $productStockData->min('stock_count');
        $ratedProducts = $productStockData->map(function ($item) use ($max, $min) {
            $normalized = $max === $min ? 5 : (($item->stock_count - $min) / ($max - $min)) * 4 + 1;
            $rating = round($normalized, 1);
            return [
                'product_id' => $item->product->id,
                'product_name' => $item->product->name,
                'product_shade_no' => $item->product->shadeNo,
                'product_purchase_shade_no' => $item->product->purchase_shade_no,
                'stock_out_count' => $item->stock_count,
                'rating' => $rating
            ];
        });
        $topRated = $ratedProducts->sortByDesc('rating')->take(5)->values();
        log::info($topRated);
        return $this->successResponse($topRated, 'Top 5 stock-out-based product ratings calculated.');
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
        if ($Product) {
            $stocks = GodownRollerStock::where(function ($query) use ($id) {
                $query->where('product_id', $id)
                    ->where(function ($q) {
                        $q->where('status', 1)
                            ->orWhereHas('cutstock', function ($subQuery) {
                                $subQuery->where('status', 1);
                            });
                    });
            })
                ->with(['products', 'products.ProductCategory'])
                ->where('type', '!=', 'gatepass')
                ->orderBy('length', 'desc')
                ->get();
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
                'type' => 0,
                'product_name' => $stock->products->name ?? 'N/A',
                'product_shadeNo' => $stock->products->shadeNo ?? 'N/A',
                'product_purchase_shade_no' => $stock->products->purchase_shade_no ?? 'N/A',
                'product_category' => $stock->products->ProductCategory->product_category ?? 'N/A',
            ];
        });

        return $this->successResponse($responseData, 'Active stocks retrieved successfully.', 200);
    }
    public function update(Request $request, $id)
    {
        $product = StockOutDetail::findOrFail($id);
        $product->update($request->all());
        $StockoutInovice = StockoutInovice::findOrFail($product->stockout_inovice_id);
        $hasPendingDetails = StockOutDetail::where('stockout_inovice_id', $StockoutInovice->id)
            ->where('status', 0)
            ->exists();
        if (!$hasPendingDetails) {
            $StockoutInovice->update(['status' => 1]);
        }

        return $this->successResponse($product, 'Product updated successfully.', 200);
    }
}
