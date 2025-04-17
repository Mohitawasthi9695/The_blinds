<?php

namespace App\Http\Controllers;

use App\Models\CutStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CutStockController extends ApiController
{
    public function index()
       {
        $cutStocks = CutStock::whereHas('stock')
            ->with(['stock.products', 'stock', 'stock.products.ProductCategory'])
            ->get();

        if ($cutStocks->isEmpty()) {
            return response()->json(['message' => 'Stock not found.'], 404);
        }
        $data = $cutStocks->map(function ($cutStock) {
            return [
                'id' => $cutStock->id,
                'godown_roller_stock_id' => $cutStock->godown_roller_stock_id,
                'sub_stock_code' => $cutStock->sub_stock_code,
                'gate_pass_no' => $cutStock->stock->gatepass->gate_pass_no,
                'width' => $cutStock->width,
                'width_unit' => optional($cutStock->stock)->width_unit,
                'length' => $cutStock->length,
                'out_length' => $cutStock->out_length,
                'length_unit' => optional($cutStock->stock)->length_unit,
                'available_length'=>$cutStock->length-$cutStock->out_length,
                'remark' => $cutStock->remark,
                'wastage' => $cutStock->wastage??0,
                'status' => $cutStock->status,
                'stock_code' => optional($cutStock->stock)->stock_code,
                'lot_no' => optional($cutStock->stock)->lot_no,
                'date' => optional($cutStock->stock)->date,
                'rack' => optional($cutStock->stock)->rack,
                'pcs' => optional($cutStock->stock)->pcs,
                'out_pcs' => optional($cutStock->stock)->out_pcs,
                'product_name' => optional($cutStock->stock->products)->name,
                'shade_no' => optional($cutStock->stock->products)->shadeNo,
                'purchase_shade_no' => optional($cutStock->stock->products)->purchase_shade_no,
                'product_category_name' => optional(optional($cutStock->stock->products)->ProductCategory)->product_category,
                'product_category_id' => optional(optional($cutStock->stock->products)->ProductCategory)->id,
            ];
        });

        return $this->successResponse($data, 'Godown Vertical Retrieved', 200);
    }
    public function getCutStock($id)
    {
        Log::info($id);

        $cutStocks = CutStock::whereHas('stock')
            ->with(['stock.products', 'stock', 'stock.products.ProductCategory'])
            ->where('godown_roller_stock_id', $id)
            ->get();

        if ($cutStocks->isEmpty()) {
            return response()->json(['message' => 'Stock not found.'], 404);
        }
        $data = $cutStocks->map(function ($cutStock) {
            return [
                'id' => $cutStock->id,
                'godown_roller_stock_id' => $cutStock->godown_roller_stock_id,
                'sub_stock_code' => $cutStock->sub_stock_code,
                'gate_pass_no' => $cutStock->stock->gatepass->gate_pass_no,
                'width' => $cutStock->width,
                'width_unit' => optional($cutStock->stock)->width_unit,
                'length' => $cutStock->length,
                'out_length' => $cutStock->out_length,
                'length_unit' => optional($cutStock->stock)->length_unit,
                'available_length'=>$cutStock->length-$cutStock->out_length,
                'remark' => $cutStock->remark,
                'wastage' => $cutStock->wastage??0,
                'status' => $cutStock->status,
                'stock_code' => optional($cutStock->stock)->stock_code,
                'lot_no' => optional($cutStock->stock)->lot_no,
                'date' => optional($cutStock->stock)->date,
                'rack' => optional($cutStock->stock)->rack,
                'pcs' => optional($cutStock->stock)->pcs,
                'out_pcs' => optional($cutStock->stock)->out_pcs,
                'product_name' => optional($cutStock->stock->products)->name,
                'shade_no' => optional($cutStock->stock->products)->shadeNo,
                'purchase_shade_no' => optional($cutStock->stock->products)->purchase_shade_no,
                'product_category_name' => optional(optional($cutStock->stock->products)->ProductCategory)->product_category,
                'product_category_id' => optional(optional($cutStock->stock->products)->ProductCategory)->id,
            ];
        });

        return $this->successResponse($data, 'Godown Vertical Retrieved', 200);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'godown_roller_stock_id' => 'required|exists:godown_roller_stocks,id',
            'sub_stock_code' => 'required|string|max:255',
            'width' => 'required|numeric',
            'length' => 'required|numeric',
            'out_length' => 'nullable|numeric',
            'remark' => 'nullable|string|max:255',
            'wastage' => 'nullable|numeric',
        ]);

        $cutStock = CutStock::create($data);

        return $this->successResponse($cutStock, 'Cut Stock Created Successfully', 201);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'godown_roller_stock_id' => 'required|exists:godown_roller_stocks,id',
            'sub_stock_code' => 'required|string|max:255',
            'width' => 'required|numeric',
            'length' => 'required|numeric',
            'out_length' => 'nullable|numeric',
            'remark' => 'nullable|string|max:255',
            'wastage' => 'nullable|numeric',
        ]);

        $cutStock = CutStock::find($id);
        if (!$cutStock) {
            return $this->errorResponse('Cut Stock not found', 404);
        }
        $cutStock->update($data);

        return $this->successResponse($cutStock, 'Cut Stock Updated Successfully', 200);
    }
    public function destroy($id)
    {
        $cutStock = CutStock::find($id);
        if (!$cutStock) {
            return $this->errorResponse('Cut Stock not found', 404);
        }
        $cutStock->delete();

        return $this->successResponse($id, 'Cut Stock Deleted Successfully', 200);
    }
}
