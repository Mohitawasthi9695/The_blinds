<?php

namespace App\Imports;

use App\Models\StocksIn;
use Maatwebsite\Excel\Concerns\ToModel;

class StockInImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new StocksIn([
            //
        ]);
    }
}
