<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use function Illuminate\Log\log;

class StockInvoice extends Seeder
{
    public function run()
    {


        $lastInvoice = DB::table('stock_invoices')->orderBy('id', 'desc')->value('invoice_no');

        // If no previous invoice exists, start from INV001
        $invoiceNumber = $lastInvoice ? (int)substr($lastInvoice, 3) : 1;
        
        $totalEntries = 100000; // Total records to insert
        $batchSize = 1000; // Number of records per batch
        
        for ($batch = 0; $batch < ($totalEntries / $batchSize); $batch++) {
            $insertData = [];
        
            for ($i = 0; $i < $batchSize; $i++) {
                $newInvoiceNo = 'INV' . str_pad($invoiceNumber++, 6, '0', STR_PAD_LEFT);
        
                $insertData[] = [
                    'invoice_no' => $newInvoiceNo,
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
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
        
            // Insert batch into database
            DB::table('stock_invoices')->insert($insertData);
            unset($insertData);
            Log::info( "Inserted " . ($batch + 1) * $batchSize . " records...\n");
        }
        
    }
}
