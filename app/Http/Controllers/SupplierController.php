<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Http\Requests\SupplierRequest;

class SupplierController extends ApiController
{

    public function index()
    {
        $suppliers = Supplier::all();
        return $this->successResponse($suppliers, 'Suppliers retrieved successfully.',200);
    }

    // POST /suppliers - Create a new supplier
    public function store(SupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());
        return $this->successResponse($supplier, 'Supplier created successfully.', 201);
    }

    // GET /suppliers/{id} - Show a single supplier
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found.', 404);
        }
        return $this->successResponse($supplier, 'Supplier retrieved successfully.',200);
    }

    // PUT /suppliers/{id} - Update a supplier
    public function update(SupplierRequest $request, $id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found.', 404);
        }

        $supplier->update($request->validated());
        return $this->successResponse($supplier, 'Supplier updated successfully.',200);
    }

    // DELETE /suppliers/{id} - Delete a supplier
    public function destroy($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found.', 404);
        }

        $supplier->delete();
        return $this->successResponse([], 'Supplier deleted successfully.',200);
    }
}
