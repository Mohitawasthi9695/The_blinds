<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVerticalStock;
use App\Http\Requests\VerticalStock;
use App\Models\GodownVerticalStock;
use Illuminate\Http\Request;

class GodownVerticalStockController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = GodownVerticalStock::with(relations: ['gatepasses', 'products', 'products.ProductCategory'])->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }

        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'gate_pass_id' => $stock->gate_pass_id,
                'gate_pass_no' => $stock->gatepasses->gate_pass_no,
                'gate_pass_date' => $stock->gatepasses->gate_pass_date,
                'product_id' => $stock->product_id,
                'stock_code' => $stock->stock_code,
                'lot_no' => $stock->lot_no,
                'length' => $stock->length,
                'out_length' => $stock->out_length,
                'get_length' => $stock->get_length,
                'length_unit' => $stock->length_unit,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return $this->successResponse($stocks,'Godown Vetical Returive',200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
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
    public function show(GodownVerticalStock $godownVerticalStock)
    {
        $stock = GodownVerticalStock::with(['gatepass', 'product', 'product.ProductCategory'])
            ->find($godownVerticalStock->id);
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
            'product_name' => optional($stock->product)->name,
            'shadeNo' => optional($stock->product)->shadeNo,
            'purchase_shade_no' => optional($stock->product)->purchase_shade_no,
            'product_category_name' => optional(optional($stock->product)->ProductCategory)->product_category,
        ];
        return $this->successResponse($data,'Godown Vetical Returive',200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVerticalStock $request, GodownVerticalStock $godownVerticalStock)
    {
        $godownVerticalStock->update($request->validated());
        return $this->successResponse($godownVerticalStock,'Godown Vetical Update',200);
    }
}
