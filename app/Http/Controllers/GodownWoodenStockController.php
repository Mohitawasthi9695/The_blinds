<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWoodenStock;
use App\Http\Requests\WoodenStock;
use App\Models\GodownWoodenStock;
use Illuminate\Support\Facades\DB;

class GodownWoodenStockController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = GodownWoodenStock::with(relations: ['gatepass', 'products', 'products.ProductCategory'])->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }

        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'gate_pass_id' => $stock->gate_pass_id,
                'gate_pass_no' => $stock->gatepass->gate_pass_no,
                'gate_pass_date' => $stock->gatepass->gate_pass_date,
                'date' => $stock->date,
                'product_id' => $stock->product_id,
                'stock_code' => $stock->stock_code,
                'lot_no' => $stock->lot_no,
                'length' => $stock->length,
                'length_unit' => $stock->length_unit,
                'width' => $stock->width,
                'width_unit' => $stock->width_unit,
                'pcs' => $stock->pcs,
                'out_pcs'=> $stock->out_pcs??0,
                'quantity'=>$stock->quantity,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return response()->json($stocks);
    }

    public function show($id)
    {
        $stocks = GodownWoodenStock::with(relations: ['gatepass', 'products', 'products.ProductCategory'])->find($id);
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        
        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'gate_pass_id' => $stock->gate_pass_id,
                'gate_pass_no' => $stock->gatepass->gate_pass_no,
                'gate_pass_date' => $stock->gatepass->gate_pass_date,
                'date' => $stock->date,
                'product_id' => $stock->product_id,
                'stock_code' => $stock->stock_code,
                'lot_no' => $stock->lot_no,
                'length' => $stock->length,
                'length_unit' => $stock->length_unit,
                'width' => $stock->width,
                'width_unit' => $stock->width_unit,
                'pcs' => $stock->pcs,
                'out_pcs'=> $stock->out_pcs??0,
                'quantity'=>$stock->quantity,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return response()->json($stocks);
    }
    public function store(WoodenStock $request)
    {
        $validatedData = $request->validated();
        try {
            $createdItems = [];

            foreach ($validatedData as $data) {
                $createdItems = GodownWoodenStock::create($data);
            }
            return $this->successResponse($createdItems, 'GodownRollerStock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create GodownRollerStock entries.', 500, $e->getMessage());
        }
    }
    public function update(UpdateWoodenStock $request, $id)
    {
        $godownGodownRollerStock = GodownWoodenStock::findOrFail($id);
        $godownGodownRollerStock->update($request->validated());
        return $this->successResponse([], 'GodownRollerStock Stock Updated', 200);
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $stock = GodownWoodenStock::findorFail($id);
            if (!$stock) {
                return response()->json(['error' => 'GodownWooden Stock  not found.'], 404);
            }
            if($stock->status!=1)
            {
                return response()->json(['error' => 'GodownWooden Stock Approved Cant able to Delete.'], 404);
            }

            $stock->delete();
            DB::commit();
            return response()->json(['success' => 'Roller Stock and related records successfully deleted.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete Roller Stock.', 'message' => $e->getMessage()], 500);
        }
    }
}
