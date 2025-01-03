<?php

namespace App\Http\Controllers;

use App\Http\Requests\OldStockRequest;
use App\Models\oldStock;
use Illuminate\Http\Request;

class OldStockController extends ApiController
{
    
    public function index()
    {
        $oldStock = oldStock::all();
        return $this->successResponse($oldStock, 'Old stock retrieved successfully.', 200);
    }
    public function store(OldStockRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $createdItems = [];

            foreach ($validatedData as $data) {
                $data['available_width'] = $data['width'];
                $data['available_height'] = $data['length'];
                $createdItems = oldStock::create($data);
            }

            return $this->successResponse($createdItems, 'Stock entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries.', 500, $e->getMessage());
        }
    }
    public function show(oldStock $oldStock)
    {
        //
    }

    public function edit(oldStock $oldStock)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, oldStock $oldStock)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(oldStock $oldStock)
    {
        //
    }
}
