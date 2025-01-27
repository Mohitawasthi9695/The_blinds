<?php

namespace App\Http\Controllers;

use App\Http\Requests\GodownAccessoryStore;
use App\Models\GodownAccessory;
use Illuminate\Http\Request;

class GodownAccessoryController extends ApiController
{
    public function index()
    {
        $warehouseAccessories = GodownAccessory::with('accessory')->get();
        return $this->successResponse($warehouseAccessories, 'GodownAccessory retrieved successfully.', 200);
    }

    public function store(GodownAccessoryStore $request)
    {
        $warehouseAccessories = $request->validated();
        $WarehouseAccessories = GodownAccessory::create($warehouseAccessories);
        return $this->successResponse($WarehouseAccessories, 'GodownAccessory created successfully.', 201);
    }
    /**
     * Display the specified resource.
     */

    public function show($id)
    {
        $GodownAccessory = GodownAccessory::with('accessory')->find($id);
        if (!$GodownAccessory) {
            return $this->errorResponse('GodownAccessory not found.', 404);
        }
        return $this->successResponse($GodownAccessory, 'ProductAccessory retrieved successfully.', 200);
    }

    public function update(Request $request, $id)
    {
        $GodownAccessory = GodownAccessory::findOrFail($id);
        $validatedData = $request->validate([
            'product_accessory_id' => 'required|exists:product_accessories,id',
            'gate_pass_id' => 'required|exists:gate_passes,id',
            'length'               => 'nullable|string|max:255',
            'unit'                 => 'nullable|string|max:255',
            'items'                => 'nullable|string|max:255',
            'box'                  => 'nullable|string|max:255',
            'quantity'             => 'nullable|string|max:255',
        ]);
        $GodownAccessory->update($validatedData);
        return $this->successResponse($GodownAccessory, 'GodownAccessory updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $GodownAccessory = GodownAccessory::find($id);
        if (!$GodownAccessory) {
            return $this->errorResponse('GodownAccessory not found.', 404);
        }
        $GodownAccessory->delete();
        return $this->successResponse([], 'GodownAccessory deleted successfully.', 200);
    }
}
