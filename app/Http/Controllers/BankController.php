<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankRequest;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $banks = Bank::all();
       return $this->successResponse($banks, 'Banks retrieved successfully.',200);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(BankRequest $request)
    {
        $Bank = Bank::create($request->validated());
        return $this->successResponse($Bank, 'Bank created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $Bank = Bank::find($id);
        if (!$Bank) {
            return $this->errorResponse('Bank not found.', 404);
        }
        return $this->successResponse($Bank, 'Bank retrieved successfully.',200);
    }

    // PUT /Banks/{id} - Update a Bank
    public function update(BankRequest $request, $id)
    {
        $Bank = Bank::find($id);
        if (!$Bank) {
            return $this->errorResponse('Bank not found.', 404);
        }

        $Bank->update($request->validated());
        return $this->successResponse($Bank, 'Bank updated successfully.',200);
    }

    // DELETE /Banks/{id} - Delete a Bank
    public function destroy($id)
    {
        $Bank = Bank::find($id);
        if (!$Bank) {
            return $this->errorResponse('Bank not found.', 404);
        }

        $Bank->delete();
        return $this->successResponse([], 'Bank deleted successfully.',200);
    }
}
