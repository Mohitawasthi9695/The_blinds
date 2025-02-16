<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVerticalStock;
use App\Http\Requests\VerticalStock;
use App\Models\GodownVerticalStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GodownVerticalStockController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = GodownVerticalStock::with(relations: ['gatepass', 'products', 'products.ProductCategory'])->get();
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
                'out_length' => $stock->out_length,
                'roll_length' => $stock->roll_length,
                'length_unit' => $stock->length_unit,
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(VerticalStock $request)
    {
        $validatedData = $request->validated();
        try {
            $createdItems = [];
            foreach ($validatedData as $data) {
                $createdItems = GodownVerticalStock::create($data);
            }
            return $this->successResponse($createdItems, 'GodownRollerStock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create GodownRollerStock entries.', 500, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        Log::info($id);
        $stock = GodownVerticalStock::with(['gatepass', 'products', 'products.ProductCategory'])
            ->find($id);
        if (!$stock) {
            return response()->json(['message' => 'Stock not found.'], 404);
        }
        $data = [
            'id' => $stock->id,
            'gate_pass_id' => $stock->gate_pass_id,
            'gate_pass_no' => optional($stock->gatepass)->gate_pass_no,
            'gate_pass_date' => optional($stock->gatepass)->gate_pass_date,
            'product_id' => $stock->product_id,
            'stock_code' => $stock->stock_code,
            'lot_no' => $stock->lot_no,
            'length' => $stock->length,
            'out_length' => $stock->out_length,
            'get_length' => $stock->get_length,
            'length_unit' => $stock->length_unit,
            'rack' => $stock->rack,
            'status' => $stock->status,
            'product_name' => optional($stock->products)->name,
            'shadeNo' => optional($stock->products)->shadeNo,
            'purchase_shade_no' => optional($stock->products)->purchase_shade_no,
            'product_category_name' => optional(optional($stock->products)->ProductCategory)->product_category,
        ];
        return $this->successResponse($data, 'Godown Vetical Returive', 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVerticalStock $request, $id)
    {
        $godownVerticalStock = GodownVerticalStock::findOrFail($id);
        $godownVerticalStock->update($request->validated());
        return $this->successResponse([], 'Godown Vertical Stock Updated', 200);
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $stock = GodownVerticalStock::findorFail($id);
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
