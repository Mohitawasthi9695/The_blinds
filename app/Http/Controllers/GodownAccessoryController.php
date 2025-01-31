<?php

namespace App\Http\Controllers;

use App\Http\Requests\GodownAccessoryStore;
use App\Models\GodownAccessory;
use App\Models\GatePass;
use App\Models\WarehouseAccessory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $GodownAccessories = GodownAccessory::create($warehouseAccessories);
        return $this->successResponse($GodownAccessories, 'GodownAccessory created successfully.', 201);
    }

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

    public function GetAllGatePass()
    {
        $stocks = GatePass::with(['warehouse_supervisors:id,name', 'godown_supervisors:id,name', 'godown_accessories'])
            ->whereHas('godown_accessories')
            ->orderBy('id', 'desc')
            ->get();
        if (!$stocks) {
            return $this->errorResponse('No GatePass Found', 404);
        }
        return $this->successResponse($stocks, 'GatePass With Godown Retreived Successfully', 200);
    }
    public function StoreAccessoryGatePass(GodownAccessoryStore $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $GatePass = GatePass::create([
                'gate_pass_no' => $validatedData['invoice_no'],
                'warehouse_supervisor_id' => Auth::id(),
                'gate_pass_date' => $validatedData['date'],
                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
            ]);

            foreach ($validatedData['out_products'] as $product) {
                $availableStock = WarehouseAccessory::where('id', $product['warehouse_accessory_id'])
                    ->where('status', '1')
                    ->first();
                if (!$availableStock) {
                    return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
                }

                if ($product['out_quantity'] > $availableStock->quantity - $availableStock->out_quantity) {
                    return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                }

                GodownAccessory::create([
                    'gate_pass_id' => $GatePass->id,
                    'warehouse_accessory_id' => $product['warehouse_accessory_id'],
                    'product_accessory_id' => $availableStock->product_accessory_id,
                    'lot_no' => $availableStock->lot_no,
                    'type' => $availableStock->type,
                    'unit	' => $product['unit	'] ?? null,
                    'items' => $product['items'] ?? null,
                    'quantity' => $product['out_quantity'] ?? null,
                    'length' => round($product['out_length'], 2),
                    'box' => $product['box'] ?? null,
                ]);

                $newQty = $availableStock->quantity - ($availableStock->out_quantity + $product['out_quantity']);
                $availableStock->update([
                    'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
                    'status' => ($newQty <= 0) ? 0 : 1,
                ]);
            }
            DB::commit();
            return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            DB::rollBack();
            return $this->successResponse('Failed to Add Gate Pass => ' . $e->getMessage(), 500);
        }
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
