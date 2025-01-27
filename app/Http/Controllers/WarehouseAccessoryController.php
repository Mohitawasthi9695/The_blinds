<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseAccessoryStore;
use App\Models\WarehouseAccessory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WarehouseAccessoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouseAccessories = WarehouseAccessory::with('accessory')->get();
        return $this->successResponse($warehouseAccessories, 'WarehouseAccessory retrieved successfully.', 200);
    }

    public function store(WarehouseAccessoryStore $request)
    {
        $warehouseAccessories = $request->validated();
        $WarehouseAccessories = WarehouseAccessory::create($warehouseAccessories);
        return $this->successResponse($WarehouseAccessories, 'WarehouseAccessory created successfully.', 201);
    }
    /**
     * Display the specified resource.
     */

    public function show($id)
    {
        $WarehouseAccessory = WarehouseAccessory::with('accessory')->find($id);
        if (!$WarehouseAccessory) {
            return $this->errorResponse('WarehouseAccessory not found.', 404);
        }
        return $this->successResponse($WarehouseAccessory, 'ProductAccessory retrieved successfully.', 200);
    }

    public function update(Request $request, $id)
    {
        $WarehouseAccessory = WarehouseAccessory::findOrFail($id);
        $validatedData = $request->validate([
            'product_accessory_id' => 'required|exists:product_accessories,id',
            'length'               => 'nullable|string|max:255',
            'unit'                 => 'nullable|string|max:255',
            'items'                => 'nullable|string|max:255',
            'box'                  => 'nullable|string|max:255',
            'quantity'             => 'nullable|string|max:255',
        ]);
        $WarehouseAccessory->update($validatedData);
        return $this->successResponse($WarehouseAccessory, 'WarehouseAccessory updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $WarehouseAccessory = WarehouseAccessory::find($id);
        if (!$WarehouseAccessory) {
            return $this->errorResponse('WarehouseAccessory not found.', 404);
        }
        $WarehouseAccessory->delete();
        return $this->successResponse([], 'WarehouseAccessory deleted successfully.', 200);
    }
}
