<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInvoiceRequest;
use App\Models\StockInvoice;
use App\Models\StockInvoiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockInvoiceController extends ApiController
{
    public function index()
    {
        $StockInvoices = StockInvoice::with(['supplier','user:id,name,phone','stock_in','stock_in.products','stock_in.products.ProductCategory'])->get();
        Log::info($StockInvoices);
        return $this->successResponse($StockInvoices, 'StockInvoices retrieved successfully.', 200);
    }

    // POST /StockInvoices - Create a new StockInvoice
    public function store(StockInvoiceRequest $request)
    {
        $validatedData = $request->validated();
        $stockInvoice = StockInvoice::create([
            'invoice_no' => $validatedData['invoice_no'],
            'supplier_id' => $validatedData['supplier_id'],
            'user_id' => Auth::id(),
            'date' => $validatedData['date'],
            'place_of_supply' => $validatedData['place_of_supply'],
            'vehicle_no' => $validatedData['vehicle_no'] ?? '-',
            'station' => $validatedData['station'] ?? '-',
            'ewaybill' => $validatedData['ewaybill'] ?? '-',
            'reverse_charge' => $validatedData['reverse_charge'] ?? false,
            'gr_rr' => $validatedData['gr_rr'] ?? '',
            'transport' => $validatedData['transport'] ?? '-',
            'agent' => $validatedData['agent']?? '-',
            'warehouse' => $validatedData['warehouse']?? '-',
            'irn' => $validatedData['irn'] ?? '-',
            'ack_no' => $validatedData['ack_no'] ?? 0,
            'ack_date' => $validatedData['ack_date'],
            'total_amount' => $validatedData['total_amount'],
            'cgst_percentage' => $validatedData['cgst_percentage'] ?? null,
            'igst_percentage' => $validatedData['igst_percentage'] ?? null,
            'sgst_percentage' => $validatedData['sgst_percentage'] ?? null,
            'qr_code' => $validatedData['qr_code'] ?? null,
        ]);
        return $this->successResponse($stockInvoice, 'StockInvoice created successfully.', 201);
    }


    // GET /StockInvoices/{id} - Show a single StockInvoice
    public function show($id)
    {
        $StockInvoice = StockInvoice::with(['supplier','user:id,name,phone','stock_in','stock_in.products','stock_in.products.ProductCategory'])->get()->find($id);
        Log::info($StockInvoice);
        if (!$StockInvoice) {
            return $this->errorResponse('StockInvoice not found.', 404);
        }
        return $this->successResponse($StockInvoice, 'StockInvoice retrieved successfully.', 200);
    }

    // PUT /StockInvoices/{id} - Update a StockInvoice
    public function update(StockInvoiceRequest $request, $id)
    {
        $stockInvoice = StockInvoice::findOrFail($id);

        $validatedData = $request->validated();

        $stockInvoice->update([
            'invoice_no' => $validatedData['invoice_no'],
            'supplier_id' => $validatedData['supplier_id'],
            'user_id' => Auth::id(),
            'date' => $validatedData['date'],
            'place_of_supply' => $validatedData['place_of_supply'],
            'vehicle_no' => $validatedData['vehicle_no'] ?? '-',
            'station' => $validatedData['station'] ?? '-',
            'ewaybill' => $validatedData['ewaybill'] ?? '-',
            'reverse_charge' => $validatedData['reverse_charge'] ?? 0,
            'gr_rr' => $validatedData['gr_rr'] ?? '',
            'transport' => $validatedData['transport'] ?? '-',
            'agent' => $validatedData['agent']?? '-',
            'warehouse' => $validatedData['warehouse']?? '-',
            'irn' => $validatedData['irn'] ?? '-',
            'ack_no' => $validatedData['ack_no'] ?? 0,
            'ack_date' => $validatedData['ack_date']?? 0,
            'total_amount' => $validatedData['total_amount'],
            'cgst_percentage' => $validatedData['cgst_percentage'] ?? 0,
            'igst_percentage' => $validatedData['igst_percentage'] ?? 0,
            'sgst_percentage' => $validatedData['sgst_percentage'] ?? 0,
            'qr_code' => $validatedData['qr_code'] ?? 0,
        ]);
        return $this->successResponse($stockInvoice, 'StockInvoice updated successfully.', 200);
    }


    // DELETE /StockInvoices/{id} - Delete a StockInvoice
    public function destroy($id)
    {
        $StockInvoice = StockInvoice::find($id);
        if (!$StockInvoice) {
            return $this->errorResponse('StockInvoice not found.', 404);
        }
        $StockInvoice->delete();
        return $this->successResponse([], 'StockInvoice deleted successfully.', 200);
    }
}
