<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class StockInvoice extends Seeder
{
    public function run()
    {
        DB::table('stock_invoices')->insert([
            [
                'invoice_no' => 'INV002',
                'supplier_id' => 1,
                'user_id' => 3,
                'date' => '2024-12-12',
                'place_of_supply' => 'Mumbai',
                'vehicle_no' => 'MH01AB1234',
                'station' => 'Station A',
                'ewaybill' => '1234567890',
                'reverse_charge' => false,
                'gr_rr' => 'RR123',
                'transport' => 'Transport Co.',
                'agent' => 'Sam Agent',
                'warehouse' => 'Warehouse A',
                'irn' => 'IRN12345',
                'ack_no' => 'ACK123',
                'ack_date' => '2024-12-10',
                'total_amount' => 15000.00,
                'cgst_percentage' => 9.00,
                'sgst_percentage' => 9.00,
                'qr_code' => 'QR12345',
                'status' => true,
                'created_at' => '2024-12-12',
                'updated_at' => Carbon::now(),
            ]
        ]);
        DB::table('stock_invoice_details')->insert([
            [
                'stock_invoice_id' => 1,
                'product_id' => 1,
                'hsn_sac_code' => '1234',
                'total_product' => 58,
                'quantity' => 10,
                'unit' => 'pcs',
                'rate' => 100.00,
                'amount' => 1000.00,
                'created_at' => '2024-12-12',
                'updated_at' => Carbon::now(),
            ],
            [
                'stock_invoice_id' => 1,
                'product_id' => 2,
                'hsn_sac_code' => '5678',
                'total_product' => 58,
                'quantity' => 20,
                'unit' => 'pcs',
                'rate' => 200.00,
                'amount' => 4000.00,
                'created_at' => '2024-12-12',
                'updated_at' => Carbon::now(),
            ]
        ]);
    }
}
