<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function index()
    {
        $Customers = Customer::all();
        return $this->successResponse($Customers, 'Customers retrieved successfully.',200);
    }

    public function store(CustomerRequest $request)
    {
        $validatedData = $request->validated();
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $filePath = $file->storeAs('logos', $fileName, 'public'); 
            $validatedData['logo'] = $filePath;
        }
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
    public function update(CustomerRequest $request, $id)
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
