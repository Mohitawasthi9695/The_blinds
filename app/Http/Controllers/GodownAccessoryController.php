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
        $godownAccessory = GodownAccessory::with('accessory')->get();
        if (!$godownAccessory) {
            return $this->errorResponse('GodownAccessory not found.', 404);
        }
        $godownAccessory = $godownAccessory->map(function ($item) {
            return [
                'id' => $item->id,
                'warehouse_accessory_id' => $item->id,
                'product_accessory_id' => $item->product_accessory_id,
                'product_category' => $item->accessory->productCategory->product_category ?? '',
                'product_accessory_name' => $item->accessory->accessory_name ?? '',
                'lot_no' => '',
                'items' => $item->items ?? '',
                'out_length' => $item->length ?? '',
                'unit' => $item->unit ?? '',
                'box' => $item->box ?? '',
                'out_quantity' => $item->out_quantity ?? 0,
                'quantity' => $item->quantity ?? 0,
                'date' => $item->created_at->format('Y-m-d'),
            ];
        });
        return $this->successResponse($godownAccessory, 'GodownAccessory retrieved successfully.', 200);
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
        $GodownAccessory = $GodownAccessory->map(function ($item) {
            return [
                'id' => $item->id,
                'warehouse_accessory_id' => $item->id,
                'product_accessory_id' => $item->product_accessory_id,
                'product_category' => $item->accessory->productCategory->product_category ?? '',
                'product_accessory_name' => $item->accessory->accessory_name ?? '',
                'lot_no' => '',
                'items' => $item->items ?? '',
                'out_length' => $item->length ?? '',
                'unit' => $item->unit ?? '',
                'box' => $item->box ?? '',
                'out_quantity' => $item->out_quantity ?? 0,
                'quantity' => $item->quantity ?? 0,
                'date' => $item->created_at->format('Y-m-d'),
            ];
        });
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
        try {
            $validatedData = $request->validated();
            log::info($validatedData);
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
                    'length' => round($product['out_length'], 2),
                    'length_unit' => $product['length_unit'] ?? null,
                    'items' => $product['items'] ?? null,
                    'quantity' => $product['out_quantity'] ?? null,
                    'box_bundle' => $product['box_bundle'] ?? null,
                ]);

                $newQty = $availableStock->quantity - ($availableStock->out_quantity + $product['out_quantity']);
                $availableStock->update([
                    'out_box_bundle'=> $availableStock->out_box_bundle + $product['box_bundle'],
                    'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
                    'status' => ($newQty <= 0) ? 0 : 1,
                ]);
            }
            return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
        } catch (\Exception $e) {
            return $this->successResponse('Failed to Add Gate Pass => ' . $e->getMessage(), 500);
        }
    }

    public function GetGatePass($id)
    {
        $stocks = GatePass::with('godowns')->where('id', $id)->first();
        return response()->json($stocks);
    }
    public function ApproveGatePass($id)
    {
        DB::beginTransaction();
        try {
            $gatePass = GatePass::where('id', $id)->first();
            if (!$gatePass) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }
            $gatePass->update(['status' => 1]);
            GodownAccessory::where('gate_pass_id', $gatePass->id)->update(['status' => 1]);
            DB::commit();
            return response()->json(['success' => 'Gate Pass approved successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to approve Gate Pass.', 'message' => $e->getMessage()], 500);
        }
    }

    public function RejectGatePass($id)
    {
        DB::beginTransaction();
        try {
            $gatePass = GatePass::where('id', $id)->first();
            if (!$gatePass) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }
            $gatePass->update(['status' => 2]);
            GodownAccessory::where('gate_pass_id', $gatePass->id)->update(['status' => 2]);
            $godownRecords = GodownAccessory::where('gate_pass_id', $gatePass->id)->get();
            foreach ($godownRecords as $godownRecord) {
                $stock = WarehouseAccessory::where('id', $godownRecord->warehouse_accessory_id )->first();
                if ($stock) {
                    $stock->update([
                        'out_quantity' => $stock->out_quantity - $godownRecord->get_quantity,
                        'status' => ($stock->quantity - $stock->out_quantity) > 0 ? 1 : 0,
                    ]);
                }
            }
            DB::commit();
            return $this->successResponse([], 'Gate Pass rejected and stock quantities rolled back successfully.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->successResponse('Failed to reject Gate Pass => ' . $e->getMessage(), 500);
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
