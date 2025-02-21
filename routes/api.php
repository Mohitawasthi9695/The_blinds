<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GatePassController;
use App\Http\Controllers\GodownAccessoryController;
use App\Http\Controllers\GodownHoneyCombStockController;
use App\Http\Controllers\GodownRollerStockController;
use App\Http\Controllers\GodownVerticalStockController;
use App\Http\Controllers\GodownWoodenStockController;
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

    Route::get('/products/category', [ProductCategoryController::class,'index']);
    Route::resource('/products', ProductController::class);
    Route::post('/product/import-csv', [ProductController::class, 'ProductCsv']);
    Route::get('/productshadeno/{category_id}', [ProductController::class, 'ProductShadeNo']);

    Route::resource('/stockin/invoice', StockInvoiceController::class);
    Route::resource('/stocks', StocksInController::class);

    Route::get('/stockout/invoiceno', [StockoutInoviceController::class, 'invoice_no']);

    Route::post('/stocks/import-csv', [StocksInController::class, 'storeFromCsv']);
    Route::get('/category/getstock/{id}', [StocksInController::class, 'GetStocks']);

    Route::get('/gatepassno', [GatePassController::class, 'GatePassNo']);
    Route::get( '/getaccessorycode', [GodownAccessoryController::class, 'Stock_code']);     
    Route::post('/godownaccessoryout', [GodownAccessoryController::class, 'StockOut']);

    Route::get('/getstocks/{id}', [StocksInController::class, 'CheckStocks']);
    Route::post('/godowns/gatepass', [GatePassController::class, 'StoreStockGatePass']);
    
    Route::get('/godowns/getStockgatepass', [GatePassController::class, 'GetAllStockGatePass']);
    Route::get('/godowns/getStockgatepass/{id}', [GatePassController::class, 'GetStockGatePass']);
    Route::delete('/godowns/gatepass/{id}', [GatePassController::class, 'DeleteGatePass']);

    Route::resource('/godownrollerstock', GodownRollerStockController::class);
    Route::resource('/godownwoodenstock', GodownWoodenStockController::class);
    Route::resource('/godownverticalstock',GodownVerticalStockController::class);
    Route::get('/godownverticalstock/stock/{id}',[GodownVerticalStockController::class,'GodownStock']);
    Route::resource('/godownhoneycombstock', GodownHoneyCombStockController::class);
   
    Route::get('/accessory/getStockgatepass', [GatePassController::class, 'GetAllAccessoryGatePass']);
    Route::get('/accessory/getStockgatepass/{id}', [GatePassController::class, 'GetAccessoryGatePass']);
    Route::put('/godowns/gatepass/{id}', [GatePassController::class, 'UpdateGatePass']);
    Route::put('/godowns/gatepass/{id}/approve', [GatePassController::class, 'ApproveStockGatePass']);
    Route::put('/godowns/gatepass/{id}/reject', [GatePassController::class, 'RejectStockGatePass']);

    // Assecorries Api

    Route::resource('/accessory', ProductAccessoryController::class);
    Route::post('/accessory/import-excel', [ProductAccessoryController::class, 'ProductAccessoryCsv']);
    Route::get('/accessory/category/{id}', [ProductAccessoryController::class, 'GetCategoryAccessory']);

    Route::resource('/warehouseAccessory', WarehouseAccessoryController::class);
    Route::post('/warehouseAccessory/import-file', [WarehouseAccessoryController::class,'storeFromCsv']);

    Route::get('/warehouse/accessory/category/{id}', [WarehouseAccessoryController::class, 'GetWarehouseAccessory']);
    Route::post('/godowns/accessory/gatepass', [GatePassController::class, 'StoreAccessoryGatePass']);
    Route::put('/godowns/accessory/gatepass/{id}/approve', [GatePassController::class, 'ApproveGatePass']);
    Route::put('/godowns/accessory/gatepass/{id}/reject', [GatePassController::class, 'RejectGatePass']);
    Route::resource('/godownAccessory', GodownAccessoryController::class);

    Route::put('godownstockout/{id}', [StockOutController::class, 'GodownStockOutApprove']);
    
    Route::get('/getgodownstocks/{id}', [StockOutController::class, 'CheckStocks']);
    Route::post('/godownstockout', [StockoutInoviceController::class, 'store']);
    Route::put('/godownstockout/approve/{id}', [StockoutInoviceController::class, 'approve']);
    Route::get('/godownstockout', [StockoutInoviceController::class,'index']);
    Route::get('/godownstockout/{id}', [StockoutInoviceController::class,'show']);
    Route::delete('/godownstockout/{id}', [StockoutInoviceController::class,'destroy']);
    Route::get('/stockout', [StockOutController::class, 'AllStockOut']);
    
    Route::get('/sales', [StockOutController::class, 'Sales']);
    // Route::get('/StockOutDash', [StockOutController::class, 'StockOutDash']);
    // Route::get('stockOuttoday', [StockoutInoviceController::class, 'stockOuttoday']);
    Route::get('/barData', [ProductController::class, 'BarGraphData']);
});
