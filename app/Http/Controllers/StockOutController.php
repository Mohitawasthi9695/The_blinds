<?php

namespace App\Http\Controllers;

use App\Models\StockOutDetail;
use Illuminate\Http\Request;

class StockOutController extends ApiController
{
    public function Sales()
    {
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();
        $startOfQuarter = now()->firstOfQuarter();
        $startOfYear = now()->startOfYear();
        $sumToday = StockOutDetail::where('created_at', '>=', $today)->sum('amount');
        $sumMonth = StockOutDetail::where('created_at', '>=', $startOfMonth)->sum('amount');
        $sumQuarter = StockOutDetail::where('created_at', '>=', $startOfQuarter)->sum('amount');
        $sumYear = StockOutDetail::where('created_at', '>=', $startOfYear)->sum('amount');
        $out_quantity = StockOutDetail::where('out_quantity', '>',0)->sum('out_quantity');
        $today_out_quantity = StockOutDetail::where('created_at', '>=', $today)->sum('out_quantity');
        $data = [
            'totals' => [
                'sum_today' => round($sumToday, 2),
                'sum_month' => round($sumMonth,2),
                'sum_quarter' => round($sumQuarter,2),
                'sum_year' => round($sumYear,2),
                'out_quantity' => round($out_quantity,0),
                'today_out_quantity' => round($today_out_quantity,0),
            ],
        ];
        return $this->successResponse($data, 'StockOutDetails and amounts retrieved successfully.');
    }

    public function StockOutDash(Request $request)
    {
        $filter = $request->query('filter', 'all'); 
        $stockOut = StockOutDetail::query();
        switch ($filter) {
            case 'today':
                $stockOut->whereDate('created_at', now()->toDateString());
                break;
            case 'this_week':
                $stockOut->whereBetween('created_at', [
                    now()->startOfWeek()->toDateTimeString(),
                    now()->endOfWeek()->toDateTimeString()
                ]);
                break;
            case 'all':
            default:
                break;
        }
        $stockOutDetails = $stockOut->get();
        return $this->successResponse($stockOutDetails, 'StockOutDetails retrieved successfully.');
    }
    
    
    
}
