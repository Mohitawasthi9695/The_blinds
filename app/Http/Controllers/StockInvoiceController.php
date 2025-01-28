<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockInvoiceRequest;
use App\Models\StockInvoice;
use App\Models\StockInvoiceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockInvoiceController extends ApiController
{
    public function index()
    {
        $StockInvoices = StockInvoice::with(['supplier','user:id,name,phone','stock_in'])->get();
        return $this->successResponse($StockInvoices, 'StockInvoices retrieved successfully.', 200);
    }

    // POST /StockInvoices - Create a new StockInvoice
    public function store(StockInvoiceRequest $request)
    {
        $validatedData = $request->validated();
        $stockInvoice = StockInvoice::create([
            'invoice_no' => $validatedData['invoice_no'],
            'people_id' => $validatedData['people_id'],
            'user_id' => Auth::id(),
            'date' => $validatedData['date'],
            'place_of_supply' => $validatedData['place_of_supply'],
            'vehicle_no' => $validatedData['vehicle_no'] ?? null,
            'station' => $validatedData['station'] ?? null,
            'ewaybill' => $validatedData['ewaybill'] ?? null,
            'reverse_charge' => $validatedData['reverse_charge'] ?? false,
            'gr_rr' => $validatedData['gr_rr'] ?? null,
            'transport' => $validatedData['transport'] ?? null,
            'agent' => $validatedData['agent'],
            'warehouse' => $validatedData['warehouse'],
            'irn' => $validatedData['irn'] ?? null,
            'ack_no' => $validatedData['ack_no'] ?? null,
            'ack_date' => $validatedData['ack_date'] ?? null,
            'total_amount' => $validatedData['total_amount'],
            'cgst_percentage' => $validatedData['cgst_percentage'] ?? null,
            'sgst_percentage' => $validatedData['sgst_percentage'] ?? null,
            'qr_code' => $validatedData['qr_code'] ?? null,
        ]);
        return $this->successResponse($stockInvoice, 'StockInvoice created successfully.', 201);
    }


    // GET /StockInvoices/{id} - Show a single StockInvoice
    public function show($id)
    {
        $StockInvoice = StockInvoice::with(['supplier', 'receiver','products'])->get()->find($id);
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
            'people_id' => $validatedData['people_id'],
            'user_id' => Auth::id(),
            'date' => $validatedData['date'],
            'place_of_supply' => $validatedData['place_of_supply'],
            'vehicle_no' => $validatedData['vehicle_no'] ?? null,
            'station' => $validatedData['station'] ?? null,
            'ewaybill' => $validatedData['ewaybill'] ?? null,
            'reverse_charge' => $validatedData['reverse_charge'] ?? false,
            'gr_rr' => $validatedData['gr_rr'] ?? null,
            'transport' => $validatedData['transport'] ?? null,
            'agent' => $validatedData['agent'],
            'warehouse' => $validatedData['warehouse'],
            'irn' => $validatedData['irn'] ?? null,
            'ack_no' => $validatedData['ack_no'] ?? null,
            'ack_date' => $validatedData['ack_date'] ?? null,
            'total_amount' => $validatedData['total_amount'],
            'cgst_percentage' => $validatedData['cgst_percentage'] ?? null,
            'sgst_percentage' => $validatedData['sgst_percentage'] ?? null,
            'bank_id' => $validatedData['bank_id'],
            'receiver_signature' => $validatedData['receiver_signature'] ?? null,
            'authorised_signatory' => $validatedData['authorised_signatory'] ?? null,
            'qr_code' => $validatedData['qr_code'] ?? null,
        ]);

        $stockInvoice->products()->delete(); 
        foreach ($validatedData['products'] as $product) {
            $stockInvoice->products()->create([
                'product_id' => $product['product_id'],
                'hsn_sac_code' => $product['hsn_sac_code'] ?? null,
                'quantity' => $product['quantity'],
                'unit' => $product['unit'] ?? null,
                'rate' => $product['rate'],
                'amount' => $product['amount'],
            ]);
        }

        return $this->successResponse($stockInvoice->load('products'), 'StockInvoice updated successfully.', 200);
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
