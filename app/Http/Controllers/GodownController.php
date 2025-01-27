<?php

namespace App\Http\Controllers;

use App\Http\Requests\GatePassUpdate;
use App\Http\Requests\GodownStockOutRequest;
use App\Http\Requests\GodownStore;
use App\Models\GatePass;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockOutDetail;
use App\Models\StockoutInovice;
use App\Models\StocksIn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class GodownController extends ApiController
{
    public function index()
    {
        $stocks = Godown::with('gatepasses:id,gate_pass_no','products')->where('status',1)->get();
        return $this->successResponse($stocks, 'GatePass With Godown Retreived Successfully', 200);
    }

    public function show($id)
    {
        $stock = Godown::with('products')->where('id', $id)->first();
        return response()->json($stock);
    }

    public function destroy(Godown $godown)
    {
        $godown->delete();
        return response()->json(['success' => 'Stock deleted successfully.']);
    }

    public function GetStockProducts()
    {
        $products = Product::whereHas('godowns', function ($query) {
            $query->where('status', 1);
        })->get();
        return response()->json($products);
    }

    public function GetStockCheckout($product_id)
    {
        $product = Product::find($product_id);

        if (!$product) {
            return $this->errorResponse('Product not found.', 404);
        }

        $stocks = $product->godowns()->where('status', 1)->with('products')->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($stock) {
            return [
                'stock_available_id' => $stock->id,
                'product_id' => $stock->product_id,
                'stock_code' => $stock->stock_code,
                'lot_no' => $stock->lot_no,
                'out_length' => $stock->available_height,
                'out_width' => $stock->get_width,
                'unit' => $stock->unit,
                'area_sq_ft' => round($stock->available_height * $stock->get_width * 10.7639, 2),
                'area' => $stock->available_height * $stock->get_width,
                'product_type' => $stock->type,
                'out_quantity' => $stock->get_quantity,
                'rack' => $stock->rack,
                'status' => $stock->status,
                'product_name' => $stock->products->name ?? 'N/A',
                'product_code' => $stock->products->code ?? 'N/A',
                'product_shadeNo' => $stock->products->shadeNo ?? 'N/A',
                'product_purchase_shade_no' => $stock->products->purchase_shade_no ?? 'N/A',
            ];
        });

        return $this->successResponse($responseData, 'Active stocks retrieved successfully.', 200);
    }
    public function GodownStockOut(GodownStockOutRequest $request)
    {
        $validatedData = $request->validated();
        log::info($validatedData);
        $stockOutInvoice = StockoutInovice::create([
            'invoice_no' => $validatedData['invoice_no'],
            'customer_id' => $validatedData['customer_id'],
            'date' => $validatedData['date'],
            'place_of_supply' => $validatedData['place_of_supply'],
            'vehicle_no' => $validatedData['vehicle_no'] ?? null,
            'station' => $validatedData['station'] ?? null,
            'ewaybill' => $validatedData['ewaybill'] ?? null,
            'reverse_charge' => $validatedData['reverse_charge'] ?? false,
            'gr_rr' => $validatedData['gr_rr'] ?? null,
            'transport' => $validatedData['transport'] ?? null,
            'receiver_id' => $validatedData['receiver_id'],
            'irn' => $validatedData['irn'] ?? null,
            'ack_no' => $validatedData['ack_no'] ?? null,
            'ack_date' => $validatedData['ack_date'] ?? null,
            'total_amount' => $validatedData['total_amount'],
            'cgst_percentage' => $validatedData['cgst_percentage'] ?? null,
            'sgst_percentage' => $validatedData['sgst_percentage'] ?? null,
            'payment_mode' => $validatedData['payment_mode'] ?? null,
            'payment_status' => $validatedData['payment_status'] ?? null,
            'payment_date' => $validatedData['payment_date'] ?? null,
            'payment_Bank' => $validatedData['payment_bank'] ?? null,
            'payment_account_no' => $validatedData['payment_account_no'] ?? null,
            'payment_ref_no' => $validatedData['payment_ref_no'] ?? null,
            'payment_amount' => $validatedData['payment_amount'] ?? null,
            'payment_remarks' => $validatedData['payment_remarks'] ?? null,
            'qr_code' => $validatedData['qr_code'] ?? null,
            'status' => 0,
        ]);

        foreach ($validatedData['out_products'] as $product) {

            $availableStock = Godown::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();
            if (!$availableStock) {
                return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
            }

            if ($availableStock->get_quantity < $product['out_quantity']) {
                return response()->json(['error' => 'Insufficient quantity available in stock.'], 422);
            }

            $outLength = $product['out_length'];
            $outWidth = $product['out_width'];
            if ($product['unit'] === 'inches') {
                $outLength *= 0.0254;
                $outWidth *= 0.0254;
            } elseif ($product['unit'] === 'feet') {
                $outLength *= 0.3048;
                $outWidth *= 0.3048;
            }
            $remainingLength = $availableStock->available_height - $outLength;
            $remainingWidth = $availableStock->available_width - $outWidth;

            $create =  StockOutDetail::create([
                'stockout_inovice_id' => $stockOutInvoice->id,
                'stock_code' => $availableStock->stock_code,
                'godown_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'product_type' => $product['product_type'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'out_quantity' => 1 ?? null,
                'out_width' => round($outWidth, 5),
                'out_length' => round($outLength, 5),
                'unit' => $product['unit'] ?? null,
                'waste_width' => $remainingWidth,
                'rack' => $availableStock->rack ?? null,
                'rate' => $product['rate'],
                'amount' => $product['amount'],
                'status' => 0,
            ]);
            $newQty = ($remainingLength <= 0) ? 0 : $availableStock->get_quantity;

            $availableStock->update([
                'available_height' => max($remainingLength, 0),
                'qty' => $newQty,
                'status' => ($remainingLength <= 0) ? 3 : 1,
            ]);
        }
        return $this->successResponse($stockOutInvoice, 'StocksInvoice created successfully.', 201);
    }
    public function GatePassNo()
    {
        $GatePass = GatePass::select('gate_pass_no')->orderBy('id', 'desc')->first();
        log::info($GatePass);
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
    public function GetAllGatePass()
    {
        log::info('GetAllGatePass');
        $stocks = GatePass::with('warehouse_supervisors:id,name','godown_supervisors:id,name','godowns')->orderBy('id', 'desc')->get();
        log::info($stocks);
        return $this->successResponse($stocks, 'GatePass With Godown Retreived Successfully', 200);
    }
    public function GetGatePass($gatePass)
    {
        $stocks = GatePass::with('godowns')->where('id', $gatePass)->first();
        return response()->json($stocks);
    }    
    public function ApproveGatePass(GatePass $gatePass)
    {
        $gatePass->update(['status' => 1]);
        $gatePass->godowns()->update(['status' => 1]);
        $gatePass->godown_accessories()->update(['status' => 1]);
        return response()->json(['success' => 'Gate Pass approved successfully.']);
    }

    public function RejectGatePass(GatePass $gatePass)
    {
        DB::beginTransaction();
        try {
            $gatePass->update(['status' => 2]);
            $gatePass->godowns()->update(['status' => 2]);
            $godownRecords = Godown::where('gate_pass_id', $gatePass->id)->get();
            foreach ($godownRecords as $godownRecord) {
                $stock = StocksIn::find($godownRecord->stock_in_id);
                if ($stock) {
                    $stock->update([
                        'out_quantity' => $stock->out_quantity - $godownRecord->get_quantity,
                        'status' => ($stock->quantity - $stock->out_quantity) > 0 ? 1 : 0,
                    ]);
                }
            }
            DB::commit(); 
            return response()->json(['success' => 'Gate Pass rejected and stock quantities rolled back successfully.']);
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json(['error' => 'Failed to reject Gate Pass.'], 500);
        }
    }

    public function StoreGatePass(GodownStore $request)
    {
        $validatedData = $request->validated();

        $GatePass = GatePass::create([
            'gate_pass_no' => $validatedData['invoice_no'],
            'warehouse_supervisor_id' => Auth::id(),
            'gate_pass_date' => $validatedData['date'],
            'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
        ]);

        foreach ($validatedData['out_products'] as $product) {
            $availableStock = StocksIn::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();
            if (!$availableStock) {
                return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
            }

            if ($product['out_quantity'] > $availableStock->quantity - $availableStock->out_quantity) {
                return $this->errorResponse("Insufficient stock available for Stock-in ID {$product['stock_available_id']}.", 400);
            }

            Godown::create([
                'gate_pass_id' => $GatePass->id,
                'stock_in_id' => $product['stock_available_id'],
                'product_id' => $product['product_id'],
                'lot_no' => $availableStock->lot_no,
                'type' => $availableStock->type,
                'product_type' => $product['product_type'] ?? null,
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'get_quantity' => $product['out_quantity'] ?? null,
                'get_width' => round($product['out_width'], 2),
                'get_length' => round($product['out_length'], 2),
                'available_height' => round($product['out_length'], 2),
                'available_width' => round($product['out_width'], 2),
                'unit' => $product['unit'] ?? null,
            ]);

            $newQty = $availableStock->quantity - ($availableStock->out_quantity + $product['out_quantity']);
            log::info($newQty);
            $availableStock->update([
                'out_quantity' => $availableStock->out_quantity+$product['out_quantity'],
                'status' => ($newQty <= 0) ? 0 : 1,
            ]);
        }

        return response()->json(['success' => 'Stock has been successfully transferred to Godown.'], 200);
    }    

    public function UpdateGatePass(GatePassUpdate $request, GatePass $gatePass)
    {
        $validatedData = $request->validated();
        $gatePass->update([
            'gate_pass_date' => $validatedData['date'],
            'godown_supervisor_id' => $validatedData['godown_supervisor_id'],
        ]);

        foreach ($validatedData['out_products'] as $product) {
            $availableStock = StocksIn::where('id', $product['stock_available_id'])
                ->where('status', '1')
                ->first();

            if (!$availableStock) {
                return response()->json(['error' => 'Stock not available for the specified product configuration.'], 422);
            }

            $godownRecord = Godown::where('gate_pass_id', $gatePass->id)
                ->where('stock_in_id', $product['stock_available_id'])->first();
            if ($godownRecord) {

                $adjustedAvailableStock = $availableStock->quantity - $availableStock->out_quantity + $godownRecord->get_quantity;
                if ($product['out_quantity'] > $adjustedAvailableStock) {
                    return response()->json(["error" => "Insufficient stock available for Stock-in ID {$product['stock_available_id']}."], 400);
                }

                $availableStock->update(['out_quantity' => $availableStock->out_quantity - $godownRecord->get_quantity]);

                $godownRecord->update([
                    'product_id' => $product['product_id'],
                    'lot_no' => $availableStock->lot_no,
                    'type' => $availableStock->type,
                    'product_type' => $product['product_type'] ?? null,
                    'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                    'get_quantity' => $product['out_quantity'] ?? null,
                    'get_width' => round($product['out_width'], 2),
                    'get_length' => round($product['out_length'], 2),
                    'available_height' => round($product['out_length'], 2),
                    'available_width' => round($product['out_width'], 2),
                    'unit' => $product['unit'] ?? null,
                ]);
            } else {
                $remainingStock = $availableStock->quantity - $availableStock->out_quantity;

                if ($product['out_quantity'] > $remainingStock) {
                    return response()->json(["error" => "Insufficient stock available for Stock-in ID {$product['stock_available_id']}."], 400);
                }

                Godown::create([
                    'gate_pass_id' => $gatePass->id,
                    'stock_in_id' => $product['stock_available_id'],
                    'product_id' => $product['product_id'],
                    'lot_no' => $availableStock->lot_no,
                    'type' => $availableStock->type,
                    'product_type' => $product['product_type'] ?? null,
                    'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                    'get_quantity' => $product['out_quantity'] ?? null,
                    'get_width' => round($product['out_width'], 2),
                    'get_length' => round($product['out_length'], 2),
                    'available_height' => round($product['out_length'], 2),
                    'available_width' => round($product['out_width'], 2),
                    'unit' => $product['unit'] ?? null,
                ]);
            }

            $newQty = $availableStock->quantity - $product['out_quantity'];

            $availableStock->update([
                'out_quantity' => $availableStock->out_quantity + $product['out_quantity'],
                'status' => ($newQty <= 0) ? 0 : 1,
            ]);
        }

        return response()->json(['success' => 'Gate Pass and stock details successfully updated.'], 200);
    }
    public function DeleteGatePass(GatePass $gatePass)
    {
        $godownRecords = Godown::where('gate_pass_id', $gatePass->id)->get();
        foreach ($godownRecords as $godownRecord) {
            $stock = StocksIn::find($godownRecord->stock_in_id);
            if ($stock) {
                $stock->update([
                    'out_quantity' => $stock->out_quantity - $godownRecord->get_quantity,
                    'status' => ($stock->quantity - ($stock->out_quantity-$godownRecord->get_quantity)) > 0 ? 1 : 0,
                ]);
            }
        }
        $gatePass->delete();
        $gatePass->godowns()->delete();
        return response()->json(['success' => 'Gate Pass and stock details successfully deleted.'], 200);
    }
    
}
