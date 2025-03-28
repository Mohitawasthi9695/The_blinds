<?php

namespace App\Http\Controllers;

use App\Http\Requests\GodownAccessoryOut;
use App\Http\Requests\GodownAccessoryStore;
use App\Models\GodownAccessory;
use App\Models\GatePass;
use App\Models\StockoutAccessory;
use App\Models\StockOutDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\UnitHelper;

class GodownAccessoryController extends ApiController
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $godownAccessory = GodownAccessory::with('gatepass:id,gate_pass_no', 'accessory');
        if ($type) {
            $godownAccessory->where('type', $type);
        }
        if ($this->role === 'sub_supervisor') {
            $godownAccessory->whereHas('gatepass', function ($query){
                $query->where('godown_supervisor_id', $this->user->id);
            });
        }
        log::info($godownAccessory->toRawSql());
        $godownAccessory = $godownAccessory->get();
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
                'transfer' => $item->transfer ?? 0,
                'quantity' => $item->quantity ?? 0,
                'status' => $item->status ?? 0,
                'date' => $item->created_at->format('Y-m-d'),
            ];
        });
        return $this->successResponse($godownAccessory, 'GodownAccessory retrieved successfully.', 200);
    }

    public function Stock_code($id)
    {
        $GatePass = GodownAccessory::select('id', 'stock_code')->where('status', 1)->where('product_accessory_id',$id)->orderBy('id', 'desc')->get();
        if ($GatePass) {
            return $this->successResponse($GatePass, 'Gdown Accessory Stock code retrieved successfully.');
        } else {
            return $this->errorResponse('Godown Has no any active accessory stock', 404);
        }
    }
    public function StockOut(GodownAccessoryOut $request)
    {
        $StockoutAccessory = $request->validated();
        log::info($StockoutAccessory);
        try {
            $createdItems = [];
            foreach ($StockoutAccessory as $data) {
                $exist = GodownAccessory::find($data['godown_accessory_id']);
                $stockout = StockOutDetail::find($data['stockout_details_id']);

                if (!$exist) {
                    return $this->errorResponse('Accessory not found in stock.', 404);
                }
                if (!$stockout) {
                    return $this->errorResponse('stockout not found in stock.', 404);
                }
                if ($exist->quantity < ($exist->out_quantity + ($data['quantity'] ?? 0))) {
                    return $this->errorResponse('Not enough stock available.', 400);
                }

                $out_length = $data['length'] ?? 0;
                $out_length = convertUnit($data['length'], $data['length_unit'], $exist->length_unit, 2);
                $out_quantity = $data['quantity'] ?? 0;

                if ($out_length*$out_quantity < $exist->length * $out_quantity) {
                    $cut_length = $exist->length*$out_quantity - $out_length*$out_quantity;
                    Log::info("Cut Length: " . $cut_length);
                    if ($cut_length > 0){
                        GodownAccessory::create([
                            'gate_pass_id' => $exist->gate_pass_id,
                            'product_accessory_id' => $exist->product_accessory_id,
                            'warehouse_accessory_id' => $exist->warehouse_accessory_id,
                            'lot_no' => $exist->lot_no ?? null,
                            'items' => 1,
                            'quantity' => $data['quantity'],
                            'length' => $cut_length,
                            'length_unit' => $exist->length_unit ?? 'N/A',
                            'out_length' => 0,
                            'box_bundle' => 1,
                            'out_box_bundle' => 0,
                            'out_quantity' => 0,
                            'rack' => $exist->rack ?? 'N/A',
                            'status' => 1,
                        ]);
                    }
                }

                $data['stock_code']=$exist->stock_code;
                $data['product_accessory_id']=$exist->product_accessory_id;
                $data['stockout_inovice_id']=$stockout->stockout_inovice_id;
                $data['rack']=$stockout->rack;
                $data['remark']=$stockout->remark??'';
                
                $createdItem = StockoutAccessory::create($data);
                $createdItems[] = $createdItem;

                // Update existing record
                $new_out_quantity = $exist->out_quantity + $out_quantity;
                $new_out_length = $exist->out_length + $out_length;

                Log::info("Total Out Quantity: " . $new_out_quantity);
                Log::info("Total Out Length: " . $new_out_length);

                $newStatus = ($new_out_quantity >= $exist->quantity) ? 2 : 1;

                $exist->update([
                    'out_quantity' => $new_out_quantity,
                    'out_length' => $new_out_length,
                    'status' => $newStatus,
                ]);
            }



            return $this->successResponse($createdItems, 'StockoutAccessory entries created successfully.', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stock entries.', 500, $e->getMessage());
        }
    }

    public function AllStockOut()
    {
        $stockOutInvoices = StockoutAccessory::with(['stockOutInvoice', 'accessory'])->get();

        if ($stockOutInvoices->isEmpty()) {
            return $this->errorResponse('No stock-out invoices found.', 404);
        }

        $formattedData = $stockOutInvoices->map(function ($item) {
            return [
                'stock_code' => $item->stock_code,
                'lot_no' => $item->lot_no,
                'accessory_name' => $item->accessory->accessory_name ?? null,
                'invoice_no' => $item->stockOutInvoice->invoice_no ?? null,
                'length' => round($item->length, 2) ?? 0,
                'width' => round($item->width, 2) ?? 0,
                'date' => $item->date ?? 0,
                'hsn_sac_code ' => $item->hsn_sac_code ?? 0,
                'pcs' => round($item->quantity) ?? 0,
                'gst' => $item->gst ?? 0,
                'rate' => round($item->rate, 2) ?? 0,
                'amount' => round($item->amount, 2) ?? 0,
                'length_unit' => $item->length_unit ?? 'N/A',
                'width_unit' => $item->width_unit ?? 'N/A',
                'rack' => $item->rack ?? 'N/A',
            ];
        });

        return $this->successResponse($formattedData, 'StockOutInvoices retrieved successfully.');
    }
    public function GetStockOut($id)
    {
        $stockOutInvoices = StockoutAccessory::with(['stockOutInvoice', 'accessory'])->where('stockout_details_id',$id)->get();

        if ($stockOutInvoices->isEmpty()) {
            return $this->errorResponse('No stock-out invoices found.', 404);
        }

        $formattedData = $stockOutInvoices->map(function ($item) {
            return [
                'stock_code' => $item->stock_code,
                'lot_no' => $item->lot_no,
                'accessory_name' => $item->accessory->accessory_name ?? null,
                'invoice_no' => $item->stockOutInvoice->invoice_no ?? null,
                'length' => round($item->length, 2) ?? 0,
                'width' => round($item->width, 2) ?? 0,
                'date' => $item->date ?? 0,
                'hsn_sac_code ' => $item->hsn_sac_code ?? 0,
                'pcs' => round($item->quantity) ?? 0,
                'gst' => $item->gst ?? 0,
                'rate' => round($item->rate, 2) ?? 0,
                'amount' => round($item->amount, 2) ?? 0,
                'length_unit' => $item->length_unit ?? 'N/A',
                'width_unit' => $item->width_unit ?? 'N/A',
                'rack' => $item->rack ?? 'N/A',
            ];
        });

        return $this->successResponse($formattedData, 'StockOutInvoices retrieved successfully.');
    }
    public function store(GodownAccessoryStore $request)
    {
        $warehouseAccessories = $request->validated();
        info($warehouseAccessories);
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
            'godown_accessory_id' => $GodownAccessory->id,
            'warehouse_accessory_id' => $GodownAccessory->warehouse_accessory_id,
            'product_accessory_id' => $GodownAccessory->product_accessory_id,
            'product_category' => $GodownAccessory->accessory->productCategory->product_category ?? '',
            'product_accessory_name' => $GodownAccessory->accessory->accessory_name ?? '',
            'lot_no' => $GodownAccessory->lot_no ?? '',
            'stock_code' => $GodownAccessory->stock_code ?? '',
            'items' => $GodownAccessory->items ?? '',
            'length' => $GodownAccessory->length ?? '',
            'length_unit' => $GodownAccessory->length_unit ?? '',
            'box_bundle' => $GodownAccessory->box_bundle ?? '',
            'quantity' => ($GodownAccessory->quantity - $GodownAccessory->out_quantity) ?? 0,
            'transfer' => $item->transfer ?? 0,
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

    public function GetTransferAccessory($id)
    {
        $user = Auth::user();
        $stocks = GodownAccessory::where('product_accessory_id', $id)
            ->where('status', 1)->whereHas('gatepass', function ($q) use ($user) {
                $q->where('godown_supervisor_id', $user->id);
            })
            ->with(['accessory', 'accessory.productCategory'])->get();

        if ($stocks->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }

        $responseData = $stocks->map(function ($item) {
            return [
                'stock_available_id'=>$item->id,
                'warehouse_accessory_id' => $item->warehouse_accessory_id,
                'product_accessory_id' => $item->product_accessory_id,
                'stock_code' => $item->stock_code,
                'lot_no' => $item->lot_no,
                'accessory_category_name' => $item->accessory->productCategory->product_category ?? null,
                'accessory_name' => $item->accessory->accessory_name ?? null,
                'length' => round($item->length, 2) ?? 0,
                'date' => $item->date ?? 0,
                'out_quantity' => round($item->quantity-($item->out_quantity+$item->transfer)) ?? 0,
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
}
