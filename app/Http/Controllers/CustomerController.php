<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Http\Requests\SupplierUpdate;
use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerController extends ApiController
{
    public function index()
    {
        $Customers = Customer::all();
        return $this->successResponse($Customers, 'Customers retrieved successfully.',200);
    }

    public function store(SupplierRequest $request)
    {
        $validatedData = $request->validated();
        $uniqueCode = Str::upper(Str::random(10));
        $validatedData['code'] = $uniqueCode;
        $Customer = Customer::create($validatedData);
        return $this->successResponse($Customer, 'Customer created successfully.', 201);
    }

    // GET /Customers/{id} - Show a single Customer
    public function show($id)
    {
        $Customer = Customer::find($id);
        if (!$Customer) {
            return $this->errorResponse('Customer not found.', 404);
        }
        return $this->successResponse($Customer, 'Customer retrieved successfully.',200);
    }

    // PUT /Customers/{id} - Update a Customer
    public function update(SupplierUpdate $request, $id)
    {
        $Customer = Customer::find($id);
        if (!$Customer) {
            return $this->errorResponse('Customer not found.', 404);
        }

        $Customer->update($request->validated());
        return $this->successResponse($Customer, 'Customer updated successfully.',200);
    }

    // DELETE /Customers/{id} - Delete a Customer
    public function destroy($id)
    {
        $Customer = Customer::find($id);
        if (!$Customer) {
            return $this->errorResponse('Customer not found.', 404);
        }

        $Customer->delete();
        return $this->successResponse([], 'Customer deleted successfully.',200);
    }
}
