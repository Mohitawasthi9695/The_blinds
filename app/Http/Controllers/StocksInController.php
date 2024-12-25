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
        $stocks = StocksIn::with(['stockProduct', 'stockInvoice'])->get();
        return response()->json($stocks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockInRequest $request)
    {
        $validatedData = $request->validated();

        if (!is_array($validatedData)) {
            return $this->errorResponse('Invalid data format. Expected an array of records.', 422);
        }

        try {
            $createdItems = [];

            foreach ($validatedData as $data) {
                if (isset($data['type']) && $data['type'] === 'roll') {
                    if (isset($data['length']) && isset($data['width'])) {
                        $data['area'] = $data['length'] * $data['width'];
                        if (isset($data['unit']) && $data['unit'] === 'meter' && isset($data['qty'])) {
                            $data['area_sq_ft'] = $data['area'] * 10.764; 
                        }else {
                            $data['area_sq_ft'] = null; 
                        }
                    } else {
                        return $this->errorResponse('Length and width are required for type "roll".', 422);
                    }
                }

                $createdItems[] = StocksIn::create($data);
            }

            return $this->successResponse($createdItems, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries.', 500, $e->getMessage());
        }
    }

    public function show($id)
    {
        $stocks = StocksIn::with(['stockProduct', 'stockInvoice'])
            ->where('invoice_id', $id)
            ->get();
        return response()->json($stocks);
    }

    public function update(StockInRequest $request, $id)
    {
        $stock = StocksIn::findOrFail($id);

        $validatedData = $request->validated();

        $stock->update($validatedData);
        return $this->successResponse($stock, 'Stock entry updated successfully.', 200);
    }

    public function destroy($id)
    {
        $stock = StocksIn::findOrFail($id);
        $stock->delete();

        return $this->successResponse(['message' => 'Stock entry deleted successfully'], 200);
    }
}
