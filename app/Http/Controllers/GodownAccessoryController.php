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
                'status' => $item->status ?? 0,
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
        log::info($StockoutAccessory);
        try {
            $createdItems = [];
            foreach ($StockoutAccessory as $data) {
                $exist = GodownAccessory::find($data['godown_accessory_id']);

                if (!$exist) {
                    return $this->errorResponse('Accessory not found in stock.', 404);
                }
                if ($exist->quantity < ($exist->out_quantity + ($data['out_quantity'] ?? 0))) {
                    return $this->errorResponse('Not enough stock available.', 400);
                }
                $out_length = $data['length'] ?? 0;
                $out_quantity = $data['out_quantity'] ?? 0;
                if ($out_length < $exist->length) {
                    $cut_length = $exist->length - $out_length;
                    Log::info("Cut Length: " . $cut_length);
                    if ($cut_length > 0) {

                        GodownAccessory::create([
                            'gate_pass_id' => $exist->gate_pass_id,
                            'product_accessory_id' => $exist->product_accessory_id,
                            'warehouse_accessory_id' => $exist->warehouse_accessory_id,
                            'lot_no' => $exist->lot_no ?? null,
                            'items' => 1,
                            'quantity' => 1,
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
                $data['gate_pass_id']=$exist->gate_pass_id;
                $data['product_accessory_id']=$exist->product_accessory_id;
                
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
                'accessory_name' => $item->accessory->name ?? null,
                'invoice_no' => $item->stockOutInvoice->invoice_no ?? null,
                'length' => round($item->length, 2) ?? 0,
                'width' => round($item->width, 2) ?? 0,
                'date' => $item->date ?? 0,
                'hsn_sac_code ' => $item->hsn_sac_code ?? 0,
                'pcs' => round($item->out_pcs) ?? 0,
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
            'available_qty' => ($GodownAccessory->quantity - $GodownAccessory->out_quantity) ?? 0,
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
