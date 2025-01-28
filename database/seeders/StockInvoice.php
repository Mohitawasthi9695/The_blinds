<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
    }
}
