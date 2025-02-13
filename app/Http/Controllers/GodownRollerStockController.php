<?php

namespace App\Http\Controllers;

use App\Models\GodownRollerStock;
use Illuminate\Http\Request;

class GodownRollerStockController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = GodownRollerStock::with(relations: ['gatepasses', 'products', 'products.ProductCategory'])->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }
        
        $stocks = $stocks->map(function ($stock) {
        $length_ft = ($stock->length_unit == 'feet') ? $stock->length : $stock->length * 3.28084;
        $length_m = ($stock->length_unit == 'meter') ? $stock->length : $stock->length / 3.28084;
        $avaible_length_ft = ($stock->length_unit == 'feet') ? $stock->available_length : $stock->available_length * 3.28084;
        $avaible_length_m = ($stock->length_unit == 'meter') ? $stock->available_length : $stock->available_length / 3.28084;
        // Convert width to feet and meters
        $width_ft = ($stock->width_unit == 'feet') ? $stock->width : $stock->width * 3.28084;
        $width_m = ($stock->width_unit == 'meter') ? $stock->width : $stock->width / 3.28084;
        $total_area_sq_ft = $length_ft * $width_ft;
        $total_area_m2 = $length_m * $width_m;
        $area_sq_ft = $width_ft*$avaible_length_ft;
        $area_m2= $width_ft*$avaible_length_m;

            return [
                'id' => $stock->id,
                'gate_pass_id' => $stock->gate_pass_id,
                'gate_pass_no' => $stock->gatepasses->gate_pass_no,
                'gate_pass_date' => $stock->gatepasses->gate_pass_date,
                'product_id' => $stock->product_id,
                'stock_code' => $stock->stock_code,
                'lot_no' => $stock->lot_no,
                'length' => $stock->length,
                'length_unit' => $stock->length_unit,
                'width' => $stock->width,
                'width_unit' => $stock->width_unit,
                'pcs' => $stock->pcs,
                'area_sq_ft' => round($area_sq_ft, 2),
                'area_m2' => round($area_m2, 2),
                'total_area_sq_ft' => round($total_area_sq_ft, 2),
                'total_area_m2' => round($total_area_m2, 2),
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(GodownRollerStock $godownRollerStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GodownRollerStock $godownRollerStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GodownRollerStock $godownRollerStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GodownRollerStock $godownRollerStock)
    {
        //
    }
}
