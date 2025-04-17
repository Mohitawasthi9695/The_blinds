<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessoryTransport;
use App\Models\GatePass;
use App\Models\GodownAccessory;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AccessoryTransferController extends ApiController
{
    public function getStock(Request $request)
    {
        $godownAccessory = GodownAccessory::with('gatepass:id,gate_pass_no','accessory_transfer_from:id,stock_code', 'accessory');
        $godownAccessory = $godownAccessory->where('type','transfer')->orderBy('id', 'desc')->get();
        if (!$godownAccessory){
            return $this->errorResponse('GodownAccessory not found.', 404);
        }
        Log::info($godownAccessory);
        $godownAccessory = $godownAccessory->map(function ($item) {
            return [
                'id' => $item->id,
                'gate_pass_no' => $item->gatepass->gate_pass_no ?? 'Added',
                'warehouse_accessory_id' => $item->id,
                'product_accessory_id' => $item->product_accessory_id,
                'product_category' => $item->accessory->productCategory->product_category ?? 'N/A',
                'product_accessory_name' => $item->accessory->accessory_name ?? 'N/A',
                'lot_no' => $item->lot_no ?? 'N/A',
                'main_stock_code' => $item->accessory_transfer_from->stock_code ?? '',
                'stock_code' => $item->stock_code ?? '',
                'items' => $item->items ?? 'N/A',
                'length' => $item->length ?? 'N/A',
                'length_unit' => $item->length_unit ?? 'N/A',
                'box_bundle' => $item->box_bundle ?? 0,
                'box_bundle_unit' => $item->box_bundle_unit ?? 'N/A',
                'out_quantity' => $item->out_quantity ?? 0,
                'transfer' => $item->transfer ?? 0,
                'remark' => $item->remark ?? 'N/A',
                'action' => ($item->godown_id==$this->user->id) ? 'in' : 'out',
                'quantity' => $item->quantity ?? 0,
                'type' => $item->type ?? 0,
                'status' => $item->status ?? 0,
                'date' => $item->date ?? '',
            ];
        });
        return $this->successResponse($godownAccessory, 'GodownAccessory retrieved successfully.', 200);
    }
    public function checkStock($id)
    {
        $stocks = GodownAccessory::where('product_accessory_id', $id)
            ->where('status', 1)
            ->where('godown_id', $this->user->id)
            ->with(['accessory', 'accessory.productCategory'])->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($item) {
            return [
                'stock_available_id' => $item->id,
                'warehouse_accessory_id' => $item->warehouse_accessory_id,
                'product_accessory_id' => $item->product_accessory_id,
                'stock_code' => $item->stock_code,
                'lot_no' => $item->lot_no,
                'accessory_category_name' => $item->accessory->productCategory->product_category ?? null,
                'accessory_name' => $item->accessory->accessory_name ?? null,
                'length' => round($item->length, 2) ?? 0,
                'date' => $item->date ?? 0,
                'out_quantity' => round($item->quantity - ($item->out_quantity + $item->transfer)) ?? 0,
                'length_unit' => $item->length_unit ?? 'N/A',
                'items' => $item->items ?? 'N/A',
                'box_bundle' => $item->box_bundle ?? 'N/A',
                'box_bundle_unit' => $item->box_bundle_unit ?? 'N/A',
                'remark' => $item->remark ?? 'N/A',
                'rack' => $item->rack ?? 'N/A',
            ];
        });

        return $this->successResponse($responseData, 'Active stocks retrieved successfully.', 200);
    }
    public function StoreTransferAccessory(AccessoryTransport $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $GatePass = GatePass::create([
                'gate_pass_no' => $validatedData['invoice_no'],
                'type' => $validatedData['type'],
                'warehouse_supervisor_id' => Auth::id(),
                'gate_pass_date' => $validatedData['date'],
                'vehicle_no' => $validatedData['vehicle_no'] ?? '',
                'place_of_supply' => $validatedData['place_of_supply'] ?? '',
                'driver_name' => $validatedData['driver_name'] ?? '',
                'driver_phone' => $validatedData['driver_phone'] ?? '',

                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
            ]);

            foreach ($validatedData['out_products'] as $product) {
                $availableStock = GodownAccessory::where('id', $product['stock_available_id'])->where('status', '1')->first();
                if (!$availableStock) {
                    DB::rollBack();
                    return response()->json(['error' => 'Stock not available for the specified configuration.'], 422);
                }
                if ($product['out_quantity'] > $availableStock->quantity - ($availableStock->out_quantity + $availableStock->transfer)) {
                    DB::rollBack();
                    return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                }
                GodownAccessory::create([
                    'gate_pass_id' => $GatePass->id,
                    'godown_id' => $validatedData['godown_supervisor_id'],
                    'warehouse_accessory_id' => $availableStock->warehouse_accessory_id,
                    'product_accessory_id' => $availableStock->product_accessory_id,
                    'lot_no' => $availableStock->lot_no,
                    'date' => $validatedData['date'],
                    'items' => $product['items'],
                    'box_bundle' => $product['box_bundle'],
                    'box_bundle_unit' => $product['box_bundle_unit'],
                    'quantity' => $product['out_quantity'],
                    'out_quantity' => 0,
                    'type' => $product['type'] ?? 'transfer',
                    'remark' => $product['remark'] ?? '',
                    'length' => round($product['length'], 2),
                    'length_unit' => $product['length_unit'],
                    'row_id' => $availableStock->id,
                ]);
                $newQty = $availableStock->quantity - ($availableStock->out_quantity + $availableStock->transfer + $product['out_quantity']);
                $availableStock->update([
                    'transfer' => $availableStock->transfer + $product['out_quantity'],
                    'status' => ($newQty <= 0) ? 2 : 1,
                ]);
            }
            DB::commit();
            return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to Add Gate Pass => ' . $e->getMessage(), 500);
        }
    }
}
