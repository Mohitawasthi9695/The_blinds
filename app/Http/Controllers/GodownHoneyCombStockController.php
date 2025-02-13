<?php

namespace App\Http\Controllers;

use App\Models\GodownHoneyCombStock;
use Illuminate\Http\Request;

class GodownHoneyCombStockController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = GodownHoneyCombStock::with(relations: ['gatepasses', 'products', 'products.ProductCategory'])->get();
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
    public function show(GodownHoneyCombStock $godownHoneyCombStock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GodownHoneyCombStock $godownHoneyCombStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GodownHoneyCombStock $godownHoneyCombStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GodownHoneyCombStock $godownHoneyCombStock)
    {
        //
    }
}
