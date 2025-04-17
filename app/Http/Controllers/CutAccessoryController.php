<?php

namespace App\Http\Controllers;

use App\Http\Requests\CutAccessoryStore;
use App\Http\Requests\CutAccessoryUpdate;
use App\Models\CutAccessory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Illuminate\Log\log;

class CutAccessoryController extends ApiController
{
    public function index(Request $request)
    {
        $godownAccessory = CutAccessory::with('stock', 'stock.accessory');
        if ($request->has('godown_accessory_id')) {
            $godownAccessory->where('godown_accessory_id', $request->query('godown_accessory_id'));
        }
        $godownAccessory = $godownAccessory->orderBy('id', 'desc')->get();
        if (!$godownAccessory) {
            return $this->errorResponse('GodownAccessory not found.', 404);
        }
        $godownAccessory = $godownAccessory->map(function ($item) {
            return [
                'id' => $item->id,
                'godown_accessory_id' => $item->godown_accessory_id,
                'product_accessory_id' => $item->stock->product_accessory_id,
                'product_category' => $item->stock->accessory->productCategory->product_category ?? 'N/A',
                'product_accessory_name' => $item->stock->accessory->accessory_name ?? 'N/A',
                'lot_no' => $item->lot_no ?? 'N/A',
                'stock_code' => $item->stock_code ?? '',
                'type' => $item->type ?? 'N/A',
                'length' => $item->length ?? 'N/A',
                'length_unit' => $item->length_unit ?? 'N/A',
                'out_quantity' => $item->out_quantity ?? 0,
                'remark' => $item->remark ?? 'N/A',
                'quantity' => $item->quantity ?? 0,
                'status' => $item->status ?? 0,
                'date' => $item->date ?? '',
            ];
        });
        return $this->successResponse($godownAccessory, 'GodownAccessory retrieved successfully.', 200);
    }
    public function store(CutAccessoryStore $request)
    {
        $data = $request->validated();
        $cutaccessory = CutAccessory::insert($data);
        return $this->successResponse($cutaccessory, 'Cut Stock Created Successfully', 201);
    }
    public function update(CutAccessoryUpdate $request, $id)
    {
        $data = $request->validated();
        $cutaccessory = CutAccessory::find($id);
        if (!$cutaccessory) {
            return $this->errorResponse('Cut Stock not found', 404);
        }
        $cutaccessory->update($data);
        return $this->successResponse($cutaccessory, 'Cut Stock Updated Successfully', 200);
    }
    public function destroy($id)
    {
        $cutaccessory=CutAccessory::find($id);
        if (!$cutaccessory) {
            return $this->errorResponse('Cut Stock not found', 404);
        }
        $cutaccessory->delete();
        return $this->successResponse($id, 'Cut Stock Deleted Successfully', 200);
    }


}
