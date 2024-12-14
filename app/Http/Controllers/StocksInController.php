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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockInRequest $request)
    {

        $validatedData = $request->validated();

        $createdItems = [];

        try {
            foreach ($validatedData as $item) {
                if ($item['qty'] > 1) {
                    $halfQty = $item['qty'] / 2;
                    $createdItem1 = StocksIn::create(array_merge($item, ['qty' => $halfQty]));
                    $createdItem2 = StocksIn::create(array_merge($item, ['qty' => $halfQty]));
                    $createdItems[] = $createdItem1;
                    $createdItems[] = $createdItem2;
                } else {
                    $createdItem = StocksIn::create($item);
                    $createdItems[] = $createdItem;
                }
            }

            return $this->successResponse($createdItems, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries: ' ,  500,$e->getMessage());
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

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StocksIn $stocksIn)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockInRequest $request, $id)
    {
        $stock = StocksIn::findOrFail($id);

        $validatedData = $request->validated();

        $stock->update($validatedData);

        return response()->json(['message' => 'Stock entry updated successfully', 'stock' => $stock]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stock = StocksIn::findOrFail($id);
        $stock->delete();

        return response()->json(['message' => 'Stock entry deleted successfully']);
    }
}
