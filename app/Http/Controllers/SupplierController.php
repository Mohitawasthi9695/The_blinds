<?php

namespace App\Http\Controllers;

use App\Models\Supplier;

use App\Http\Controllers\ApiController;
use App\Http\Requests\SupplierRequest;
use App\Http\Requests\SupplierUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends ApiController
{

    public function index(Request $request)
    {
        $condition = $request->status;
        if ($condition == 1) {
            $peoples = Supplier::where('status', 1)->get();
        } else {
            $peoples = Supplier::all();
        }
        return $this->successResponse($peoples, 'peoples retrieved successfully.', 200);
    }
    public function supplierStocks()
    {
        $supplier = Supplier::with('stockInvoices.products')->select('id', 'name')->find(5);

        return $this->successResponse($supplier, 'peoples retrieved successfully.', 200);
    }
    public function RecentSuppliers()
    {
        $peoples = Supplier::whereHas('RecentInvoice')
            ->with('RecentInvoice')->select('id', 'name', 'gst_no', 'owner_mobile', 'reg_address')
            ->get();
        return $this->successResponse($peoples, 'peoples retrieved successfully.', 200);
    }
    public function store(SupplierRequest $request)
    {
        $validatedData = $request->validated();
        $uniqueCode = Str::upper(Str::random(10));
        $validatedData['code'] = $uniqueCode;
        $supplier = Supplier::create($validatedData);
        return $this->successResponse($supplier, 'Supplier created successfully.', 201);
    }

    // GET /peoples/{id} - Show a single supplier
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found.', 404);
        }
        return $this->successResponse($supplier, 'Supplier retrieved successfully.', 200);
    }

    public function update(SupplierUpdate $request, $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found.', 404);
        }
        $validatedData = $request->validated();
        $supplier->update($validatedData);

        return $this->successResponse($supplier, 'Supplier updated successfully.', 200);
    }


    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found.', 404);
        }

        $supplier->delete();
        return $this->successResponse([], 'Supplier deleted successfully.', 200);
    }
}
