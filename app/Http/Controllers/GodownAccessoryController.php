<?php

namespace App\Http\Controllers;

use App\Http\Requests\GodownAccessoryOut;
use App\Http\Requests\GodownAccessoryStore;
use App\Models\GodownAccessory;
use App\Models\GatePass;
use App\Models\StockoutAccessory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GodownAccessoryController extends ApiController
{
    public function index()
    {
        $godownAccessory = GodownAccessory::with('gatepass:id,gate_pass_no', 'accessory')->get();
        log::info($godownAccessory);
        if (!$godownAccessory) {
            return $this->errorResponse('GodownAccessory not found.', 404);
        }
        $godownAccessory = $godownAccessory->map(function ($item) {
            return [
                'id' => $item->id,
                'gate_pass_no' => $item->gatepass->gate_pass_no ?? 'N/A',
                'warehouse_accessory_id' => $item->id,
                'product_accessory_id' => $item->product_accessory_id,
                'product_category' => $item->accessory->productCategory->product_category ?? 'N/A',
                'product_accessory_name' => $item->accessory->accessory_name ?? 'N/A',
                'lot_no' => $item->lot_no ?? 'N/A',
                'stock_code' => $item->stock_code ?? '',
                'items' => $item->items ?? 'N/A',
                'length' => $item->length ?? 'N/A',
                'length_unit' => $item->length_unit ?? 'N/A',
                'box_bundle' => $item->box_bundle ?? 0,
                'out_quantity' => $item->out_quantity ?? 0,
                'quantity' => $item->quantity ?? 0,
                'date' => $item->created_at->format('Y-m-d'),
            ];
        });
        return $this->successResponse($godownAccessory, 'GodownAccessory retrieved successfully.', 200);
    }

    public function Stock_code()
    {
        $GatePass = GodownAccessory::select('id', 'stock_code')->where('status', 1)->orderBy('id', 'desc')->get();
        if ($GatePass) {
            return $this->successResponse($GatePass, 'Gdown Accessory Stock code retrieved successfully.');
        } else {
            return $this->errorResponse('Godown Has no any active accessory stock', 404);
        }
    }
    public function StockOut(GodownAccessoryOut $request)
    {
        $StockoutAccessory = $request->validated();
        try {
            $createdItems = [];
            foreach ($StockoutAccessory as $data) {
                $createdItems = StockoutAccessory::create($data);
            }
            return $this->successResponse($createdItems, 'StockoutAccessory entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries.', 500, $e->getMessage());
        }
    }
    public function store(GodownAccessoryStore $request)
    {
        $warehouseAccessories = $request->validated();
        log:info($warehouseAccessories);
        $GodownAccessories = GodownAccessory::create($warehouseAccessories);
        return $this->successResponse($GodownAccessories, 'GodownAccessory created successfully.', 201);
    }

    public function show($id)
    {
        $GodownAccessory = GodownAccessory::with('accessory')->find($id);

        if (!$GodownAccessory) {
            return $this->errorResponse('GodownAccessory not found.', 404);
        }
        $response = [
            'id' => $GodownAccessory->id,
            'warehouse_accessory_id' => $GodownAccessory->id,
            'product_accessory_id' => $GodownAccessory->product_accessory_id,
            'product_category' => $GodownAccessory->accessory->productCategory->product_category ?? 'N/A',
            'product_accessory_name' => $GodownAccessory->accessory->accessory_name ?? 'N/A',
            'lot_no' => $GodownAccessory->lot_no ?? 'N/A',
            'stock_code' => $GodownAccessory->stock_code ?? '',
            'items' => $GodownAccessory->items ?? 'N/A',
            'out_length' => $GodownAccessory->length ?? 'N/A',
            'unit' => $GodownAccessory->unit ?? 'N/A',
            'box_bundle' => $GodownAccessory->box_bundle ?? 'N/A',
            'out_quantity' => $GodownAccessory->out_quantity ?? 0,
            'quantity' => $GodownAccessory->quantity ?? 0,
            'date' => $GodownAccessory->created_at->format('Y-m-d'),
        ];

        return $this->successResponse($response, 'ProductAccessory retrieved successfully.', 200);
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



    public function GetGatePass($id)
    {
        $stocks = GatePass::with('godowns')->where('id', $id)->first();
        return response()->json($stocks);
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
