<?php

namespace App\Http\Controllers;

use App\Http\Requests\GatePassUpdate;
use App\Http\Requests\GodownStore;
use App\Http\Requests\TransferStore;
use App\Models\GodownAccessory;
use App\Models\GodownRollerStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\UnitHelper;
use App\Models\WarehouseAccessory;
use App\Http\Requests\GodownAccessoryStore;
use App\Models\StocksIn;
use App\Models\Godown;
use App\Models\GatePass;
use Illuminate\Support\Facades\Log;


class GatePassController extends ApiController
{

    public function GatePassNo()
    {
        $GatePass = GatePass::select('gate_pass_no')->orderBy('id', 'desc')->first();
        if ($GatePass) {
            $GatePassNo = $GatePass->gate_pass_no;
            $prefix = substr($GatePassNo, 0, 2);
            $number = (int) substr($GatePassNo, 2);
            $newNumber = $number + 1;
            $gate_pass_no = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        } else {
            $gate_pass_no = 'GT0001';
        }
        return $this->successResponse($gate_pass_no, 'Invoice number retrieved successfully.');
    }
    public function GetAllStockGatePass()
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();
        log::info($role);
        $stocks = GatePass::with([
            'warehouse_supervisors:id,name',
            'godown_supervisors:id,name',
            'godown_roller_stock.stocks:id,stock_code',
            'godown_roller_stock.products',
        ])->where('type', 'stock');

        if ($role === 'sub_supervisor') {
            $stocks->where('godown_supervisor_id', $user->id);
        }
        log::info($stocks->toSql());
        $stocks = $stocks->orderBy('id', 'desc')->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No GatePass Found', 404);
        }

        // Transforming the data to merge stocks
        $formattedStocks = $stocks->map(function ($stock) {
            $allStock = collect()
                ->merge($stock->godown_roller_stock)
                ->values()->map(function ($stockItem) {
                    return [
                        'id' => $stockItem->id,
                        'gate_pass_id' => $stockItem->gate_pass_id,
                        'stock_in_id' => $stockItem->stock_in_id,
                        'product_id' => $stockItem->product_id,
                        'stockin_code' => $stockItem->stocks->stock_code ?? null,
                        'stock_code' => $stockItem->stock_code ?? null,
                        'date' => $stockItem->date,
                        'lot_no' => $stockItem->lot_no,
                        'width' => round($stockItem->width, 2),
                        'width_unit' => $stockItem->width_unit,
                        'length' => round($stockItem->length, 2),
                        'length_unit' => $stockItem->length_unit,
                        'pcs' => $stockItem->pcs ?? 1,
                        'quantity' => $stockItem->quantity,
                        'status' => $stockItem->status,
                        'products_shadeNo' => $stockItem->products->shadeNo,
                        'products_purchase_shade_no' => $stockItem->products->purchase_shade_no,
                    ];
                });
            return [
                'id' => $stock->id,
                'gate_pass_no' => $stock->gate_pass_no,
                'gate_pass_date' => $stock->gate_pass_date,
                'vehicle_no' => $stock->vehicle_no,
                'place_of_supply' => $stock->place_of_supply,
                'driver_name' => $stock->driver_name,
                'driver_phone' => $stock->driver_phone,
                'warehouse_supervisor' => $stock->warehouse_supervisors,
                'godown_supervisor' => $stock->godown_supervisors,
                'status' => $stock->status,
                'all_stocks' => $allStock,
            ];
        });
        return $this->successResponse($formattedStocks, 'GatePass With Stock Retrieved Successfully', 200);
    }
    public function GetStockGatePass($id)
    {
        $stocks = GatePass::with([
            'warehouse_supervisors:id,name',
            'godown_supervisors:id,name',
            'godown_roller_stock.stocks:id,stock_code',
            'godown_roller_stock.products',
        ])->where('id', $id)->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No GatePass Found', 404);
        }

        // Transforming the data to merge stocks and extract stock_code
        $formattedStocks = $stocks->map(function ($stock) {
            $allStock = collect()
                ->merge($stock->godown_roller_stock)
                ->values()
                ->map(function ($stockItem) {
                    return [
                        'id' => $stockItem->id,
                        'gate_pass_id' => $stockItem->gate_pass_id,
                        'stock_in_id' => $stockItem->stock_in_id,
                        'product_id' => $stockItem->product_id,
                        'stockin_code' => $stockItem->stocks->stock_code ?? null,
                        'stock_code' => $stockItem->stock_code ?? null,
                        'date' => $stockItem->date,
                        'lot_no' => $stockItem->lot_no,
                        'width' => $stockItem->width,
                        'width_unit' => $stockItem->width_unit,
                        'length' => $stockItem->length,
                        'length_unit' => $stockItem->length_unit,
                        'pcs' => $stockItem->pcs ?? 1,
                        'quantity' => $stockItem->quantity,
                        'status' => $stockItem->status,
                        'products_shadeNo' => $stockItem->products->shadeNo,
                        'products_purchase_shade_no' => $stockItem->products->purchase_shade_no,
                    ];
                });
            return [
                'id' => $stock->id,
                'gate_pass_no' => $stock->gate_pass_no,
                'gate_pass_date' => $stock->gate_pass_date,
                'vehicle_no' => $stock->vehicle_no,
                'place_of_supply' => $stock->place_of_supply,
                'driver_name' => $stock->driver_name,
                'driver_phone' => $stock->driver_phone,
                'warehouse_supervisor' => $stock->warehouse_supervisors,
                'godown_supervisor' => $stock->godown_supervisors,
                'all_stocks' => $allStock,
            ];
        });
        return $this->successResponse($formattedStocks, 'GatePass With Stock Retrieved Successfully', 200);
    }

    public function ApproveStockGatePass($id)
    {
        DB::beginTransaction();
        try {
            $gatePass = GatePass::with(['godown_roller_stock'])->where('id', $id)->first();
            if (!$gatePass) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }
            $gatePass->update(['status' => 1]);
            $gatePass->godown_roller_stock()->update(['status' => 1]);
            DB::commit();
            return response()->json(['success' => 'Gate Pass approved successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to approve Gate Pass.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function StoreStockGatePass(GodownStore $request)
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
                'gate_pass_time' => now(),
                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
            ]);

            foreach ($validatedData['out_products'] as $product) {

                $availableStock = StocksIn::where('id', $product['stock_available_id'])->where('status', '1')->first();
                if (!$availableStock) {
                    DB::rollBack();
                    return response()->json(['error' => 'Stock not available for the specified configuration.'], 422);
                }
                if ($product['out_quantity'] > $availableStock->quantity - $availableStock->out_quantity) {
                    DB::rollBack();
                    return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                }

                $outQuantity = $product['out_quantity'] ?? 1;
                    for ($i = 0; $i < $outQuantity; $i++) {
                        if($availableStock->product_category_id==3){
                            $product['type'] = 'gatepass';
                        }
                        GodownRollerStock::create([
                            'gate_pass_id' => $GatePass->id,
                            'stock_in_id' => $product['stock_available_id'],
                            'product_category_id' => $availableStock->product_category_id,
                            'product_id' => $availableStock->product_id,
                            'lot_no' => $availableStock->lot_no,
                            'date' => $validatedData['date'],
                            'quantity' => 1,
                            'pcs' => $product['pcs'],
                            'width' => round($product['width'],2),
                            'length' => round($product['length'],  2),
                            'width_unit' => $product['width_unit'],
                            'length_unit' => $product['length_unit'],
                            'user_id' => Auth::id(),
                        ]);
                    }
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
            return $this->errorResponse('Failed to Add Gate Pass => ' . $e->getMessage(), 500);
        }
    }
    public function GetAllAccessoryGatePass()
    {
        $stocks = GatePass::with(['warehouse_supervisors:id,name', 'godown_supervisors:id,name', 'godown_accessories','godown_accessories.accessory'])->whereHas('godown_accessories')
            ->orderBy('id', 'desc')
            ->get();
        if (!$stocks) {
            return $this->errorResponse('No GatePass Found', 404);
        }
        return $this->successResponse($stocks, 'GatePass With Godown Retreived Successfully', 200);
    }
    public function GetAccessoryGatePass($id)
    {
        $stocks = GatePass::with(['warehouse_supervisors:id,name', 'godown_supervisors:id,name', 'godown_accessories','godown_accessories.accessory'])->whereHas('godown_accessories')
            ->where('id', $id)->get();
        if (!$stocks) {
            return $this->errorResponse('No GatePass Found', 404);
        }
        return $this->successResponse($stocks, 'GatePass With Godown Retreived Successfully', 200);
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
                $stock = WarehouseAccessory::where('id', $godownRecord->warehouse_accessory_id)->first();
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
    public function StoreAccessoryGatePass(GodownAccessoryStore $request)
    {
        try {
            $validatedData = $request->validated();
            log::info($validatedData);
            $GatePass = GatePass::create([
                'gate_pass_no' => $validatedData['invoice_no'],
                'warehouse_supervisor_id' => Auth::id(),
                'gate_pass_date' => $validatedData['date'],
                'type' => $validatedData['type'],
                'vehicle_no' => $validatedData['vehicle_no'] ?? '',
                'place_of_supply' => $validatedData['place_of_supply'] ?? '',
                'driver_name' => $validatedData['driver_name'] ?? '',
                'driver_phone' => $validatedData['driver_phone'] ?? '',
                'gate_pass_time' => now(),
                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
            ]);

            foreach ($validatedData['out_products'] as $product) {
                $availableStock = WarehouseAccessory::where('id', $product['warehouse_accessory_id'])
                    ->where('status', '1')
                    ->first();
                if (!$availableStock) {
                    return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
                }
                if ($product['box_bundle'] > $availableStock->box_bundle - $availableStock->out_box_bundle) {
                    return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                }
                GodownAccessory::create([
                    'gate_pass_id' => $GatePass->id,
                    'warehouse_accessory_id' => $product['warehouse_accessory_id'],
                    'product_accessory_id' => $availableStock->product_accessory_id,
                    'lot_no' => $availableStock->lot_no,
                    'length' => round($product['length'], 2),
                    'length_unit' => $product['length_unit'] ?? null,
                    'items' => $product['items'] ?? null,
                    'quantity' => $product['quantity'] ?? null,
                    'box_bundle' => $product['box_bundle'] ?? null,
                    'box_bundle_unit' => $product['box_bundle_unit'] ?? '',
                ]);

                $newQty = $availableStock->box_bundle - ($availableStock->out_box_bundle + $product['box_bundle']);
                $availableStock->update([
                    'out_box_bundle' => $availableStock->out_box_bundle + $product['box_bundle'],
                    'status' => ($newQty <= 0) ? 0 : 1,
                ]);
            }
            return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
        } catch (\Exception $e) {
            return $this->successResponse('Failed to Add Gate Pass => ' . $e->getMessage(), 500);
        }
    }
    public function DeleteGatePass($id)
    {
        DB::beginTransaction();
        try {
            $gatePass = GatePass::find($id);
            if (!$gatePass) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }

            if ($gatePass->status == 1) {
                return response()->json(['error' => 'Approved Gate Pass cannot be deleted.'], 403);
            }
            $godownStocks = [
                GodownRollerStock::where('gate_pass_id', $id)->get(),
            ];
            foreach ($godownStocks as $stockGroup) {
                foreach ($stockGroup as $stock) {
                    $availableStock = StocksIn::where('id', $stock->stock_in_id)->first();
                    if ($availableStock) {
                        $availableStock->update([
                            'out_quantity' => max(0, $availableStock->out_quantity - $stock->quantity),
                            'status' => 1,
                        ]);
                    }
                    $stock->delete();
                }
            }
            $gatePass->delete();
            DB::commit();
            return response()->json(['success' => 'Gate Pass and associated records deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete Gate Pass.', 'message' => $e->getMessage()], 500);
        }
    }
    public function StoreTransferGatePass(TransferStore $request)
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
                'gate_pass_time' => now(),
                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
            ]);

            foreach ($validatedData['out_products'] as $product) {

                $availableStock = GodownRollerStock::where('id', $product['stock_available_id'])->where('status', '1')->first();
                if (!$availableStock) {
                    DB::rollBack();
                    return response()->json(['error' => 'Stock not available for the specified configuration.'], 422);
                }
                if ($product['pcs'] > $availableStock->pcs - ($availableStock->out_pcs+$availableStock->transfer)) {
                    DB::rollBack();
                    return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                }
                    GodownRollerStock::create([
                        'gate_pass_id' => $GatePass->id,
                        'stock_in_id' => $product['stock_in_id'],
                        'product_category_id' => $availableStock->product_category_id,
                        'product_id' => $availableStock->product_id,
                        'lot_no' => $availableStock->lot_no,
                        'date' => $validatedData['date'],
                        'quantity' => 1,
                        'pcs' => $product['pcs'],
                        'width' => round($product['width'], 2),
                        'length' => round($product['length'],  2),
                        'width_unit' => $product['width_unit'],
                        'length_unit' => $product['length_unit'],
                        'user_id' => Auth::id(),
                    ]);
                $newQty = $availableStock->pcs - ($availableStock->out_pcs + $availableStock->transfer + $product['pcs']);
                $availableStock->update([
                    'transfer' => $availableStock->transfer + $product['pcs'],
                    'status' => ($newQty <= 0) ? 0 : 1,
                ]);
            }
            DB::commit();
            return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to Add Gate Pass => ' . $e->getMessage(), 500);
        }
    }
    public function GetTransferGatePass()
    {
        $user = Auth::user();
        $role = $user->getRoleNames()->first();
        log::info($role);
        $stocks = GatePass::with([
            'warehouse_supervisors:id,name',
            'godown_supervisors:id,name',
            'godown_roller_stock.stocks:id,stock_code',
            'godown_roller_stock.products',
        ])->where('type', 'transfer');
        log::info($stocks->toSql());
        $stocks = $stocks->orderBy('id', 'desc')->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No GatePass Found', 404);
        }

        // Transforming the data to merge stocks
        $formattedStocks = $stocks->map(function ($stock) {
            $type = ($stock->warehouse_supervisor_id === Auth::id()) ? 1 : 2;
            $allStock = collect()
            ->merge($stock->godown_roller_stock)
            ->values()->map(function ($stockItem) {
                return [
                'id' => $stockItem->id,
                'gate_pass_id' => $stockItem->gate_pass_id,
                'stock_in_id' => $stockItem->stock_in_id,
                'product_id' => $stockItem->product_id,
                'stockin_code' => $stockItem->stocks->stock_code ?? null,
                'stock_code' => $stockItem->stock_code ?? null,
                'date' => $stockItem->date,
                'lot_no' => $stockItem->lot_no,
                'width' => round($stockItem->width, 2),
                'width_unit' => $stockItem->width_unit,
                'length' => round($stockItem->length, 2),
                'length_unit' => $stockItem->length_unit,
                'pcs' => $stockItem->pcs ?? 1,
                'quantity' => $stockItem->quantity,
                'status' => $stockItem->status,
                'products_shadeNo' => $stockItem->products->shadeNo,
                'products_purchase_shade_no' => $stockItem->products->purchase_shade_no,
                ];
            });
            return [
            'id' => $stock->id,
            'gate_pass_no' => $stock->gate_pass_no,
            'gate_pass_date' => $stock->gate_pass_date,
            'vehicle_no' => $stock->vehicle_no,
            'place_of_supply' => $stock->place_of_supply,
            'driver_name' => $stock->driver_name,
            'driver_phone' => $stock->driver_phone,
            'warehouse_supervisor' => $stock->warehouse_supervisors,
            'godown_supervisor' => $stock->godown_supervisors,
            'status' => $stock->status,
            'type' => $type,
            'all_stocks' => $allStock,
            ];
        });
        return $this->successResponse($formattedStocks, 'GatePass With Stock Retrieved Successfully', 200);
    }
}
