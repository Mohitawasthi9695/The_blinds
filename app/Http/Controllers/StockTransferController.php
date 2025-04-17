<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferStore;
use App\Models\GatePass;
use App\Models\GodownRollerStock;
use DB;
use Illuminate\Http\Request;

class StockTransferController extends ApiController
{
    public function GetTransferStocks($id)
    {
        $stocks = GodownRollerStock::where('product_id', $id)
            ->where('status', 1)->whereHas('gatepass')->where('godown_id', $this->user->id)
            ->with(['products', 'products.ProductCategory'])->where('type','!=','gatepass')->get();
        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($stock) {
            return [
                'stock_available_id' => $stock->id,
                'stock_in_id' => $stock->stock_in_id,
                'lot_no' => $stock->lot_no,
                'stock_code' => $stock->stock_code,
                'length' => round($stock->length, 2) ?? '',
                'width' => round($stock->width, 2) ?? '',
                'length_unit' => $stock->length_unit ?? 'N/A',
                'width_unit' => $stock->width_unit ?? 'N/A',
                'pcs' => ($stock->pcs - ($stock->out_pcs+$stock->transfer)) ?? 0,
                'rack' => $stock->rack,
                'remark' => $stock->remark,
                'product_name' => $stock->products->name ?? 'N/A',
                'product_shadeNo' => $stock->products->shadeNo ?? 'N/A',
                'product_purchase_shade_no' => $stock->products->purchase_shade_no ?? 'N/A',
                'product_category' => $stock->products->ProductCategory->product_category ?? 'N/A',
            ];
        });

        return $this->successResponse($responseData, 'Active stocks retrieved successfully.', 200);
    }
    public function GetTransferedStock(Request $request)
    {
        $stocks = GodownRollerStock::with(['gatepass', 'products', 'products.ProductCategory'])->where('type', 'transfer');
        $stocks= $stocks->orderBy('id', 'desc')->get();
        if($stocks->isEmpty()) {
            return $this->errorResponse('No stocks found.', 404);
        }

        $stocks = $stocks->map(function ($stock) {
            return [
                'id' => $stock->id,
                'gate_pass_id' => $stock->gate_pass_id,
                'gate_pass_no' => $stock->gatepass->gate_pass_no,
                'gate_pass_date' => $stock->gatepass->gate_pass_date,
                'date' => $stock->date,
                'product_id' => $stock->product_id,
                'sub_stock_code' => $stock->sub_stock_code ?? '',
                'stock_code' => $stock->stock_code,
                'action' => ($stock->godown_id == $this->user->id) ? 'in' : 'out',
                'lot_no' => $stock->lot_no,
                'type' => $stock->type,
                'length' => $stock->length,
                'out_length' => $stock->out_length ?? 0,
                'length_unit' => $stock->length_unit,
                'width' => $stock->width,
                'width_unit' => $stock->width_unit,
                'rack' => $stock->rack,
                'pcs' => $stock->pcs,
                'out_pcs' => $stock->out_pcs,
                'wastage' => $stock->wastage ?? 0,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? null,
                'shadeNo' => $stock->products->shadeNo ?? null,
                'purchase_shade_no' => $stock->products->purchase_shade_no ?? null,
                'product_category_name' => $stock->products->ProductCategory->product_category ?? null,
            ];
        });
        return response()->json($stocks);
    }
    public function StoreTransferGatePass(TransferStore $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();
            $GatePass = GatePass::create([
                'gate_pass_no' => $validatedData['invoice_no'],
                'type' => $validatedData['type'],
                'warehouse_supervisor_id' => $this->user->id,
                'gate_pass_date' => $validatedData['date'],
                'vehicle_no' => $validatedData['vehicle_no'] ?? '',
                'place_of_supply' => $validatedData['place_of_supply'] ?? '',
                'driver_name' => $validatedData['driver_name'] ?? '',
                'driver_phone' => $validatedData['driver_phone'] ?? '',
                'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
            ]);

            foreach ($validatedData['out_products'] as $product) {

                $availableStock = GodownRollerStock::where('id', $product['stock_available_id'])->where('status', '1')->first();
                if (!$availableStock) {
                    DB::rollBack();
                    return response()->json(['error' => 'Stock not available for the specified configuration.'], 422);
                }
                if ($product['pcs'] > $availableStock->pcs - ($availableStock->out_pcs + $availableStock->transfer)) {
                    DB::rollBack();
                    return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
                }
                GodownRollerStock::create([
                    'gate_pass_id' => $GatePass->id,
                    'stock_in_id' => $product['stock_in_id'],
                    'godown_id' =>  $validatedData['godown_supervisor_id'],
                    'product_category_id' => $availableStock->product_category_id,
                    'product_id' => $availableStock->product_id,
                    'lot_no' => $availableStock->lot_no,
                    'date' => $validatedData['date'],
                    'quantity' => 1,
                    'pcs' => $product['pcs'],
                    'type' => $product['type'] ?? 'transfer',
                    'width' => round($product['width'], 2),
                    'length' => round($product['length'], 2),
                    'width_unit' => $product['width_unit'],
                    'length_unit' => $product['length_unit'],
                    'user_id' => $this->user->id,
                    'row_id' => $availableStock->id,
                ]);
                $newQty = $availableStock->pcs - ($availableStock->out_pcs + $availableStock->transfer + $product['pcs']);
                $availableStock->update([
                    'transfer' => $availableStock->transfer + $product['pcs'],
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
