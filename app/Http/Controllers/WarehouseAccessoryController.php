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

    public function GetWarehouseAccessory($id)
    {
        $warehouseAccessories = WarehouseAccessory::with('accessory', 'accessory.productCategory')->where('product_accessory_id', $id)->where('status', 1)->get();
        if ($warehouseAccessories->isEmpty()) {
            return $this->errorResponse('WarehouseAccessory not found.', 404);
        }
        $formattedData = $warehouseAccessories->map(function ($item) {
            return [
                'warehouse_accessory_id' => $item->id, 
                'product_accessory_id'=>$item->product_accessory_id,
                'product_category' => $item->accessory->productCategory->product_category ?? '',
                'product_accessory_name' => $item->accessory->accessory_name ?? '',
                'lot_no' => '', 
                'items' => $item->items ?? '', 
                'out_length' => $item->length ?? '',
                'unit' => $item->unit ?? '',
                'box' => $item->box ?? '',
                'out_quantity' => $item->out_quantity ?? $item->quantity ?? 0
            ];
        });
        
        return $this->successResponse($formattedData, 'WarehouseAccessory retrieved successfully.', 200);
    }
    public function store(WarehouseAccessoryStore $request)
    {
        $warehouseAccessories = $request->validated(); 
        $insertedAccessories = WarehouseAccessory::insert($warehouseAccessories);
        return $this->successResponse($insertedAccessories, 'WarehouseAccessories created successfully.', 201);
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
