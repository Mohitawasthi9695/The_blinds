<?php

namespace App\Http\Controllers;

use App\Models\Supplier;

use App\Http\Controllers\ApiController;
use App\Http\Requests\SupplierRequest;
use App\Http\Requests\SupplierUpdateRequest;
use Illuminate\Support\Str;
class SupplierController extends ApiController
{

    public function index()
    {
        $suppliers = Supplier::all();
        return $this->successResponse($suppliers, 'Suppliers retrieved successfully.', 200);
    }

    // POST /suppliers - Create a new supplier
    public function store(SupplierRequest $request)
    {
        // Validate request data
        $validatedData = $request->validated();
        $uniqueCode= Str::upper(Str::random(10));
        $validatedData['code'] = $uniqueCode;
        // Handle logo file upload if it exists
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            // Generate a unique file name for the uploaded logo
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('logos', $fileName, 'public'); // Store file in 'storage/app/public/logos'

            // Add the file path to the validated data
            $validatedData['logo'] = $filePath;
        }

        // Create a new supplier with the validated data
        $supplier = Supplier::create($validatedData);

        // Return a success response with the created supplier
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

    // PUT /suppliers/{id} - Update a supplier
    public function update(SupplierUpdateRequest $request, $id)
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
        return $this->successResponse([], 'Supplier deleted successfully.', 200);
    }
}
