<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GodownAccessoryController;
use App\Http\Controllers\GodownController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\ProductAccessoryController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockInvoiceController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockoutInoviceController;
use App\Http\Controllers\StocksInController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseAccessoryController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('forgot-password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/change-password', [ForgotPasswordController::class, 'changepassword']);
    Route::resource('/peoples', PeopleController::class);
    Route::get('/sub_supervisor', [UserController::class, 'Sub_supervisor']);
    Route::get('/data', [PeopleController::class, 'supplierStocks']);
    Route::get('/recent-peoples', [PeopleController::class, 'RecentPeoples']);
    Route::resource('/products/category', ProductCategoryController::class);
    Route::resource('/products', ProductController::class);
    Route::post('/product/import-csv', [ProductController::class, 'ProductCsv']);

    Route::resource('/stockin/invoice', StockInvoiceController::class);

    Route::get('/stockout/invoiceno', [StockoutInoviceController::class, 'invoice_no']);

    Route::resource('/stocks', StocksInController::class);
    Route::get('/category/rollstock', [StocksInController::class, 'CategoryRollStocks']);
    Route::get('/category/boxstock', [StocksInController::class, 'CategoryBoxStocks']);

    Route::get('/stocks_insrm', [StocksInController::class, 'stocks_ins']);
    Route::get('/godownsrm', [GodownController::class, 'godowns']);
    Route::get('/stockout_inovicesrm', [StockoutInoviceController::class, 'stockout_inovices']);
    Route::get('/stock_out_details', [StockOutController::class, 'stock_out_details']);

    Route::get('/gatepass/shadeno/{category_id}', [ProductController::class, 'GatePassShadeNo']);
    Route::get('/godowns/gatepassno', [GodownController::class, 'GatePassNo']);
    Route::get('/stockin/{product_id}', [StocksInController::class, 'CheckStocks']);
    Route::resource('/godown', GodownController::class);

    Route::get('/godowns/getStockgatepass', [GodownController::class, 'GetAllGatePass']);
    Route::get('/godowns/getAccessorygatepass', [GodownAccessoryController::class, 'GetAllGatePass']);
    Route::post('/godowns/gatepass', [GodownController::class, 'StoreGatePass']);
    Route::get('/godowns/gatepass/{id}', [GodownController::class, 'GetGatePass']);
    Route::put('/godowns/gatepass/{id}', [GodownController::class, 'UpdateGatePass']);
    Route::delete('/godowns/gatepass/{id}', [GodownController::class, 'DeleteGatePass']);
    Route::put('/godowns/gatepass/{id}/approve', [GodownController::class, 'ApproveGatePass']);
    Route::put('/godowns/gatepass/{id}/reject', [GodownController::class, 'RejectGatePass']);
    
    // Assecorries Api
    
    Route::resource('/accessory', ProductAccessoryController::class);
    Route::get('/accessory/category/{id}', [ProductAccessoryController::class,'GetCategoryAccessory']);
    
    Route::resource('/warehouse/accessory', WarehouseAccessoryController::class);
    Route::get('/warehouse/accessory/category/{id}', [WarehouseAccessoryController::class,'GetWarehouseAccessory']); 
    Route::post('godowns/accessory/gatepass', [GodownAccessoryController::class, 'StoreAccessoryGatePass']);
    
    Route::resource('/godown/accessory', GodownAccessoryController::class);

    Route::get('/Cproducts', [GodownController::class, 'GetStockProducts']);
    Route::get('/godowncheckout/{id}', [GodownController::class, 'GetStockCheckout']);
    Route::post('godownstockout', [GodownController::class, 'GodownStockOut']);
    Route::put('godownstockout/{id}', [StockOutController::class, 'GodownStockOutApprove']);


    
    Route::resource('stockout', StockoutInoviceController::class);
    Route::get('/admin/allstockout', [StockoutInoviceController::class, 'AllStockOut']);
    Route::resource('admin/stockout', StockoutInoviceController::class);
    Route::get('/sales', [StockOutController::class, 'Sales']);
    Route::get('/StockOutDash', [StockOutController::class, 'StockOutDash']);
    Route::get('/allstockout', [StockoutInoviceController::class, 'AllStockOut']);
    Route::get('stockOuttoday', [StockoutInoviceController::class, 'stockOuttoday']);
    Route::get('/barData', [ProductController::class, 'BarGraphData']);
    Route::post('/stocks/import-csv', [StocksInController::class, 'storeFromCsv']);

});
