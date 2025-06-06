<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseAccessoryStore;
use App\Models\ProductAccessory;
use App\Models\WarehouseAccessory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WarehouseAccessoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $warehouseAccessories = WarehouseAccessory::with('accessory')->get();
        log::info($warehouseAccessories);
        $warehouseAccessories = $warehouseAccessories->map(function ($item) {
            return [
                'id' => $item->id,
                'warehouse_accessory_id' => $item->id,
                'product_accessory_id' => $item->product_accessory_id,
                'product_category' => $item->accessory->productCategory->product_category ?? '',
                'product_accessory_name' => $item->accessory->accessory_name ?? '',
                'lot_no' => $item->lot_no ?? 'N/A',
                'date' => $item->date ?? 'N/A',
                'stock_code' => $item->stock_code ?? 'N/A',
                'items' => $item->items ?? 'N/A',
                'length' => $item->length ?? 'N/A',
                'length_unit' => $item->length_unit ?? 'N/A',
                'box_bundle' => $item->box_bundle ?? 0,
                'out_box_bundle' => $item->out_box_bundle ?? 0,
                'box_bundle_unit' => $item->box_bundle_unit ?? 'N/A',
                'quantity' => $item->items*$item->box_bundle ?? 0,
                'out_quantity' => $item->items*$item->out_box_bundle ?? 0,
            ];
        });

        return $this->successResponse($warehouseAccessories, 'WarehouseAccessory retrieved successfully.', 200);
    }

    public function GetWarehouseAccessory($id)
    {
        $warehouseAccessories = WarehouseAccessory::with('accessory', 'accessory.productCategory')->where('product_accessory_id', $id)->where('status', 1)->get();
        if ($warehouseAccessories->isEmpty()) {
            return $this->errorResponse('No active stocks found for this product.', 404);
        }
        $formattedData = $warehouseAccessories->map(function ($item) {
            return [
                'warehouse_accessory_id' => $item->id,
                'product_accessory_id' => $item->product_accessory_id,
                'product_category' => $item->accessory->productCategory->product_category ?? '',
                'product_accessory_name' => $item->accessory->accessory_name ?? '',
                'stock_code' => $item->stock_code ?? '',
                'lot_no' => $item->lot_no ?? '',
                'date' => $item->date ?? '',
                'items' => $item->items ?? '',
                'length' => $item->length ?? '',
                'length_unit' => $item->length_unit ?? '',
                'box_bundle' => $item->box_bundle - $item->out_box_bundle ?? 0,
                'out_box_bundle' => $item->out_box_bundle ?? 0,
                'box_bundle_unit' => $item->box_bundle_unit ?? '',
                'quantity' => $item->items * ($item->box_bundle - $item->out_box_bundle) ?? 0,

            ];
        });

        return $this->successResponse($formattedData, 'WarehouseAccessory retrieved successfully.', 200);
    }
    public function store(WarehouseAccessoryStore $request)
    {
        $warehouseAccessories = $request->validated();
        $now = Carbon::now();
        foreach ($warehouseAccessories as &$accessory) {
            $accessory['created_at'] = $now;
            $accessory['updated_at'] = $now;
        }
        $insertedAccessories = WarehouseAccessory::insert($warehouseAccessories);
        return $this->successResponse($insertedAccessories, 'WarehouseAccessories created successfully.', 201);
    }
    /**
     * Display the specified resource.
     */

    public function storeFromCsv(Request $request)
    {
        Log::info($request->all());

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        DB::beginTransaction();
        try {
            $data = Excel::toArray([], $request->file('csv_file'));

            if (empty($data) || !isset($data[0])) {
                return response()->json(['error' => 'File is empty or invalid'], 422);
            }

            $rows = $data[0];
            $createdItems = [];

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;
                }
                if (empty($row[1]) || empty($row[2])) {
                    DB::rollBack();
                    return response()->json(['error' => 'Required fields product_accessory_name or lot_no are missing'], 422);
                }
                $accessory_name = $row[1];
                $lotNo = $row[2];

                $ProductAccessory = ProductAccessory::where('accessory_name', $accessory_name)->first();

                if (!$ProductAccessory) {
                    DB::rollBack();
                    return response()->json(['error' => "ProductAccessory with name {$accessory_name} not found"], 422);
                }
                $data = [
                    'product_accessory_id'=> $ProductAccessory->id,
                    'lot_no'         => $lotNo,
                    'length'         => $row[3] ?? null,
                    'length_unit'    => $row[4] ?? null,
                    'items'          => $row[5] ?? null,
                    'box_bundle'     => $row[6] ?? null,
                    'box_bundle_unit'=> $row[7] ?? null,
                    'remark'     => $row[8] ?? null,
                    'date' => isset($row[9]) && is_numeric($row[9]) 
                    ? date('Y-m-d', strtotime("1899-12-30 +{$row[9]} days")) : Carbon::today(),
                ];
                $createdItem = WarehouseAccessory::create($data);
                $createdItems[] = $createdItem;
            }

            DB::commit();
            return $this->successResponse($createdItems, 'WarehouseAccessory entries created successfully.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process file', 'message' => $e->getMessage()], 500);
        }
    }


    public function show($id)
    {
        $WarehouseAccessory = WarehouseAccessory::with('accessory')->find($id);
        if (!$WarehouseAccessory) {
            return $this->errorResponse('WarehouseAccessory not found.', 404);
        }
        return $this->successResponse($WarehouseAccessory, 'ProductAccessory retrieved successfully.', 200);
    }

    public function update(Request $request, $id)
    {
        $WarehouseAccessory = WarehouseAccessory::findOrFail($id);
        $validatedData = $request->validate([
            'product_accessory_id' => 'required|exists:product_accessories,id',
            'lot_no' => 'nullable|string|max:255',
            'length' => 'nullable|numeric|min:0',
            'length_unit' => 'nullable|string|max:255',
            'items' => 'nullable|numeric|min:0',
            'box_bundle' => 'nullable|numeric|min:0',
            'box_bundle_unit' => 'nullable|string|max:255',
            'quantity' => 'nullable|numeric|min:0',
        ]);
        $WarehouseAccessory->update($validatedData);
        return $this->successResponse($WarehouseAccessory, 'WarehouseAccessory updated successfully.', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $WarehouseAccessory = WarehouseAccessory::find($id);
        if (!$WarehouseAccessory) {
            return $this->errorResponse('WarehouseAccessory not found.', 404);
        }
        $WarehouseAccessory->delete();
        return $this->successResponse([], 'WarehouseAccessory deleted successfully.', 200);
    }
}
