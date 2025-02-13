<?php

namespace App\Http\Controllers;

use App\Http\Requests\GatePassUpdate;
use App\Http\Requests\GodownStore;
use App\Models\GodownAccessory;
use App\Models\GodownHoneyCombStock;
use App\Models\GodownRollerStock;
use App\Models\GodownVerticalStock;
use App\Models\GodownWoodenStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        $stocks = GatePass::with([
            'warehouse_supervisors:id,name',
            'godown_supervisors:id,name',
            'godown_roller_stock.products',
            'godown_wooden_stock.products',
            'godown_vertical_stock.products',
            'godown_honeycomb_stock.products'
        ])->where('type','stock')->orderBy('id', 'desc')->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No GatePass Found', 404);
        }

        // Transforming the data to merge stocks
        $formattedStocks = $stocks->map(function ($stock) {
            $allStock = collect()
                ->merge($stock->godown_roller_stock)
                ->merge($stock->godown_wooden_stock)
                ->merge($stock->godown_vertical_stock)
                ->merge($stock->godown_honeycomb_stock)
                ->values();

            return [
                'id' => $stock->id,
                'gate_pass_no' => $stock->gate_pass_no,
                'gate_pass_date' => $stock->gate_pass_date,
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
            'godown_roller_stock.products',
            'godown_wooden_stock.products',
            'godown_vertical_stock.products',
            'godown_honeycomb_stock.products'
        ])->where('id', $id)->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No GatePass Found', 404);
        }

        // Transforming the data to merge stocks
        $formattedStocks = $stocks->map(function ($stock) {
            $allStock = collect()
                ->merge($stock->godown_roller_stock)
                ->merge($stock->godown_wooden_stock)
                ->merge($stock->godown_vertical_stock)
                ->merge($stock->godown_honeycomb_stock)
                ->values();

            return [
                'id' => $stock->id,
                'gate_pass_no' => $stock->gate_pass_no,
                'gate_pass_date' => $stock->gate_pass_date,
                'warehouse_supervisor' => $stock->warehouse_supervisors,
                'godown_supervisor' => $stock->godown_supervisors,
                'all_stocks' => $allStock,
            ];
        });
        return $this->successResponse($formattedStocks, 'GatePass With Godown Retreived Successfully', 200);
    }
    public function ApproveStockGatePass($id)
    {
        DB::beginTransaction();
        try {
            $gatePass = GatePass::with([
                'godown_roller_stock',
                'godown_wooden_stock',
                'godown_vertical_stock',
                'godown_honeycomb_stock'
            ])->where('id', $id)->first();

            if (!$gatePass) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }

            // Update GatePass status
            $gatePass->update(['status' => 1]);
            $gatePass->godown_roller_stock()->update(['status' => 1]);
            $gatePass->godown_wooden_stock()->update(['status' => 1]);
            $gatePass->godown_vertical_stock()->update(['status' => 1]);
            $gatePass->godown_honeycomb_stock()->update(['status' => 1]);

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

    public function RejectStockGatePass($id)
    {
        DB::beginTransaction();
        try {
            $gatePass = GatePass::where('id', $id)->first();
            if (!$gatePass) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }
            $gatePass->update(['status' => 2]);
            DB::commit();
            return $this->successResponse([], 'Gate Pass rejected and stock quantities rolled back successfully.', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->successResponse('Failed to reject Gate Pass => ' . $e->getMessage(), 500);
        }
    }
    public function StoreStockGatePass(GodownStore $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            log::info($validatedData);
            $GatePass = GatePass::create([
                'gate_pass_no' => $validatedData['invoice_no'],
                'type'=>$validatedData['type'],
                'warehouse_supervisor_id' => Auth::id(),
                'gate_pass_date' => $validatedData['date'],
                'gate_pass_time' => now(),
                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
            ]);

            foreach ($validatedData['out_products'] as $product) {
                $availableStock = StocksIn::where('id', $product['stock_available_id'])
                    ->where('status', '1')
                    ->first();
                log::info($availableStock);
                if (!$availableStock) {
                    DB::rollBack();
                    return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
                }

                if ($product['out_quantity'] > $availableStock->quantity - $availableStock->out_quantity) {
                    DB::rollBack();
                    return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                }

                $outQuantity = $product['out_quantity'] ?? 1;
                if ($availableStock->product_category_id == 1) {
                    for ($i = 0; $i < $outQuantity; $i++) {
                        GodownRollerStock::create([
                            'gate_pass_id' => $GatePass->id,
                            'stock_in_id' => $product['stock_available_id'],
                            'product_category_id' => $availableStock->product_category_id,
                            'product_id' => $availableStock->product_id,
                            'lot_no' => $availableStock->lot_no,
                            'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                            'quantity' => 1,
                            'width' => round($product['width'], 2),
                            'length' => round($product['length'], 2),
                            'width_unit' => $product['width_unit'] ?? null,
                            'length_unit' => $product['length_unit'] ?? null,
                            'user_id' => Auth::id(),
                        ]);
                    }
                    $newQty = $availableStock->quantity - ($availableStock->out_quantity + $product['out_quantity']);
                    $availableStock->update([
                        'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
                        'status' => ($newQty <= 0) ? 0 : 1,
                    ]);
                }
                if ($availableStock->product_category_id == 2) {
                    for ($i = 0; $i < $outQuantity; $i++) {
                        GodownWoodenStock::create([
                            'gate_pass_id' => $GatePass->id,
                            'stock_in_id' => $product['stock_available_id'],
                            'product_category_id' => $availableStock->product_category_id,
                            'product_id' => $availableStock->product_id,
                            'lot_no' => $availableStock->lot_no,
                            'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                            'quantity' => 1,
                            'pcs' => $product['pcs'] ?? null,
                            'width' => round($product['width'], 2),
                            'length' => round($product['length'], 2),
                            'width_unit' => $product['width_unit'] ?? null,
                            'length_unit' => $product['length_unit'] ?? null,
                            'user_id' => Auth::id(),
                        ]);
                    }
                    $newQty = $availableStock->quantity - ($availableStock->out_quantity + $product['out_quantity']);
                    $availableStock->update([
                        'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
                        'status' => ($newQty <= 0) ? 0 : 1,
                    ]);
                }
                if ($availableStock->product_category_id == 3) {
                    for ($i = 0; $i < $outQuantity; $i++) {
                        GodownVerticalStock::create([
                            'gate_pass_id' => $GatePass->id,
                            'stock_in_id' => $product['stock_available_id'],
                            'product_category_id' => $availableStock->product_category_id,
                            'product_id' => $availableStock->product_id,
                            'lot_no' => $availableStock->lot_no,
                            'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                        
                            'quantity' => 1,
                            'length' => round($product['length'], 2),
                            'length_unit' => $product['length_unit'] ?? 'meter',
                            'user_id' => Auth::id(),
                        ]);
                    }
                    $newQty = $availableStock->quantity - ($availableStock->out_quantity + $product['out_quantity']);
                    $availableStock->update([
                        'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
                        'status' => ($newQty <= 0) ? 0 : 1,
                    ]);
                }
                if ($availableStock->product_category_id == 4) {
                    for ($i = 0; $i < $outQuantity; $i++) {
                        GodownHoneyCombStock::create([
                            'gate_pass_id' => $GatePass->id,
                            'stock_in_id' => $product['stock_available_id'],
                            'product_category_id' => $availableStock->product_category_id,
                            'product_id' => $availableStock->product_id,
                            'lot_no' => $availableStock->lot_no,
                            'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                            'quantity' => 1,
                            'width' => round($product['width'], 2),
                            'length' => round($product['length'], 2),
                            'width_unit' => $product['width_unit'] ?? null,
                            'length_unit' => $product['length_unit'] ?? null,
                            'user_id' => Auth::id(),
                        ]);
                    }
                    $newQty = $availableStock->quantity - ($availableStock->out_quantity + $product['out_quantity']);
                    $availableStock->update([
                        'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
                        'status' => ($newQty <= 0) ? 0 : 1,
                    ]);
                }
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
        $stocks = GatePass::with(['warehouse_supervisors:id,name', 'godown_supervisors:id,name', 'godown_accessories'])->whereHas('godown_accessories')
            ->orderBy('id', 'desc')
            ->get();
        if (!$stocks) {
            return $this->errorResponse('No GatePass Found', 404);
        }
        return $this->successResponse($stocks, 'GatePass With Godown Retreived Successfully', 200);
    }
    public function GetAccessoryGatePass($id)
    {
        $stocks = GatePass::with(['warehouse_supervisors:id,name', 'godown_supervisors:id,name', 'godown_accessories'])->whereHas('godown_accessories')
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
                    'out_box_bundle' => $availableStock->out_box_bundle + $product['box_bundle'],
                    'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
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
            $gatePass = GatePass::where('id', $id)->first();
            if (!$gatePass) {
                return response()->json(['error' => 'Gate Pass not found.'], 404);
            }
            $gatePass->delete();
            DB::commit();
            return response()->json(['success' => 'Gate Pass and related records successfully deleted.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to delete Gate Pass.', 'message' => $e->getMessage()], 500);
        }
    }
}
