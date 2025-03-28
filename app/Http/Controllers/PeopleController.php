<?php

namespace App\Http\Controllers;

use App\Http\Requests\PeopleStore;
use App\Http\Requests\PeopleUpdate;
use App\Models\People;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PeopleController extends ApiController
{
    public function index(Request $request)
    {
        $condition = $request->people_type;
        $peoples = People::where('people_type', $condition)->get();
        return $this->successResponse($peoples, 'Peoples retrieved successfully.', 200);
    }
    public function supplierStocks()
    {
        $supplier = People::with('stockInvoices.products')->where('people_type', 'Supplier')->select('id', 'name')->find(5);

        return $this->successResponse($supplier, 'Peoples retrieved successfully.', 200);
    }
    public function RecentSupplier()
    {
        $peoples = People::whereHas('RecentInvoice')
            ->with('RecentInvoice')->select('id', 'name', 'gst_no', 'owner_mobile', 'reg_address')
            ->get();
        return $this->successResponse($peoples, 'Peoples retrieved successfully.', 200);
    }
    public function store(PeopleStore $request)
    {
        $validatedData = $request->validated();
        $uniqueCode = Str::upper(Str::random(10));
        $validatedData['code'] = $uniqueCode;
        $supplier = People::create($validatedData);
        return $this->successResponse($supplier, 'People created successfully.', 201);
    }

    // GET /peoples/{id} - Show a single supplier
    public function show($id)
    {
        $supplier = People::find($id);
        if (!$supplier) {
            return $this->errorResponse('People not found.', 404);
        }
        return $this->successResponse($supplier, 'People retrieved successfully.', 200);
    }

    public function update(PeopleUpdate $request, $id)
    {
        $supplier = People::find($id);
        if (!$supplier) {
            return $this->errorResponse('People not found.', 404);
        }
        $validatedData = $request->validated();
        $supplier->update($validatedData);

        return $this->successResponse($supplier, 'People updated successfully.', 200);
    }


    public function destroy($id)
    {
        $supplier = People::find($id);
        if (!$supplier) {
            return $this->errorResponse('People not found.', 404);
        }

        $supplier->delete();
        return $this->successResponse([], 'People deleted successfully.', 200);
    }
}
