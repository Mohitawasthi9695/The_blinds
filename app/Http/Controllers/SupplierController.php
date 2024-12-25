<?php

namespace App\Http\Controllers;

use App\Models\Supplier;

use App\Http\Controllers\ApiController;
use App\Http\Requests\SupplierRequest;
use App\Http\Requests\SupplierUpdateRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class SupplierController extends ApiController
{

    public function index()
    {
        $suppliers = Supplier::all();
        return $this->successResponse($suppliers, 'Suppliers retrieved successfully.', 200);
    }

    public function store(SupplierRequest $request)
    {
        $validatedData = $request->validated();
        $uniqueCode= Str::upper(Str::random(10));
        $validatedData['code'] = $uniqueCode;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('logos', $fileName, 'public'); 
            $validatedData['logo'] = $filePath;
        }
        $supplier = Supplier::create($validatedData);
        return $this->successResponse($supplier, 'Supplier created successfully.', 201);
    }

    // GET /suppliers/{id} - Show a single supplier
    public function show($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) {
            return $this->errorResponse('Supplier not found.', 404);
        }
        return $this->successResponse($supplier, 'Supplier retrieved successfully.', 200);
    }

    public function update(SupplierRequest $request, $id)
{
    $supplier = Supplier::find($id);
    if (!$supplier) {
        return $this->errorResponse('Supplier not found.', 404);
    }
    $validatedData = $request->validated();

    if ($request->hasFile('logo')) {
        if ($supplier->logo && Storage::disk('public')->exists($supplier->logo)) {
            Storage::disk('public')->delete($supplier->logo);
        }
        $file = $request->file('logo');
        $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
        $filePath = $file->storeAs('logos', $fileName, 'public');
        $validatedData['logo'] = $filePath;
    }
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
