<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInRequest;
use App\Models\StocksIn;
use Illuminate\Http\Request;

class StocksInController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stocks = StocksIn::with(['stockInvoiceDetails', 'stockInvoice'])->get();
        return response()->json($stocks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockInRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $createdItem = StocksIn::create($validatedData);    
            return $this->successResponse($createdItem, 'Stock entry created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entry.', 500, $e->getMessage());
        }
    }

        /**
         * Display the specified resource.
         */
    public function show($id)
    {
        $stock = StocksIn::with(['stockInvoiceDetails', 'stockInvoice'])->findOrFail($id);
        return response()->json($stock);
    }

    public function update(StockInRequest $request, $id)
    {
        $stock = StocksIn::findOrFail($id);

        $validatedData = $request->validated();

        $stock->update($validatedData);
        return $this->successResponse($stock, 'Stock entry updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stock = StocksIn::findOrFail($id);
        $stock->delete();

        return $this->successResponse(['message' => 'Stock entry deleted successfully'],200);
    }
}
