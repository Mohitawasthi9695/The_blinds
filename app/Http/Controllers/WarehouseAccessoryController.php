<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseAccessoryStore;
use App\Models\WarehouseAccessory;
use Illuminate\Http\Request;

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
        return $this->successResponse($WarehouseAccessories, 'Product created successfully.', 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(WarehouseAccessory $warehouseAccessory)
    {
        $warehouseAccessory = WarehouseAccessory::with('accessory')->find($warehouseAccessory->id);
        if (!$warehouseAccessory) {
            return $this->errorResponse('WarehouseAccessory not found.', 404);
        }
        return $this->successResponse($warehouseAccessory, 'WarehouseAccessory retrieved successfully.', 200);
    }

    public function update(Request $request, WarehouseAccessory $warehouseAccessory)
    {
        $WarehouseAccessory = WarehouseAccessory::findOrFail($warehouseAccessory->id);
        $WarehouseAccessory->update($request->all());
        return $this->successResponse($WarehouseAccessory, 'ProductAccessory updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WarehouseAccessory $warehouseAccessory)
    {
        $warehouseAccessory = WarehouseAccessory::find($warehouseAccessory->id);
        if (!$warehouseAccessory) {
            return $this->errorResponse('warehouseAccessory not found.', 404);
        }
        $warehouseAccessory->delete();
        return $this->successResponse([], 'warehouseAccessory deleted successfully.', 200);
    }
}
