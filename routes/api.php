<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GatePassController;
use App\Http\Controllers\GodownAccessoryController;
use App\Http\Controllers\GodownRollerStockController;
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
    Route::get('/recent-suppliers', [PeopleController::class, 'RecentSupplier']);

    Route::get('/products/category', [ProductCategoryController::class,'index']);
    Route::put('/products/category/{id}', [ProductCategoryController::class,'update']);
    Route::delete('/products/category/{id}', [ProductCategoryController::class,'destroy']);
    Route::post('/products/category', [ProductCategoryController::class,'store']);
    Route::resource('/products', ProductController::class)->except(['destroy']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('can:product.delete');
    
    Route::post('/product/import-csv', [ProductController::class, 'ProductCsv']);
    Route::get('/productshadeno/{category_id}', [ProductController::class, 'ProductShadeNo']);

    Route::resource('/stockin/invoice', StockInvoiceController::class);
    Route::resource('/stocks', StocksInController::class);

    Route::get('/stockout/invoiceno', [StockoutInoviceController::class, 'invoice_no']);

    Route::post('/stocks/import-csv', [StocksInController::class, 'storeFromCsv']);
    Route::get('/category/getstock/{id}', [StocksInController::class, 'GetStocks']);

    Route::get('/gatepassno', [GatePassController::class, 'GatePassNo']);
    Route::get( '/getaccessorycode/{id}', [GodownAccessoryController::class, 'Stock_code']);     
    Route::post('/godownaccessoryout', [GodownAccessoryController::class, 'StockOut']);

    Route::get('/getstocks/{id}', [StocksInController::class, 'CheckStocks']);
    Route::post('/godowns/gatepass', [GatePassController::class, 'StoreStockGatePass']);
    
    Route::get('/godowns/getStockgatepass', [GatePassController::class, 'GetAllStockGatePass']);
    Route::get('/godowns/getStockgatepass/{id}', [GatePassController::class, 'GetStockGatePass']);
    Route::delete('/godowns/gatepass/{id}', [GatePassController::class, 'DeleteGatePass']);

    Route::get('/godownstock', [GodownRollerStockController::class,'index']);
    Route::get('/godownstock/{id}', [GodownRollerStockController::class,'show']);
    Route::get('/godownstock/cutstock/{id}', [GodownRollerStockController::class,'GetCutStock']);
    Route::put('/godownstock/{id}', [GodownRollerStockController::class,'update']);
    Route::delete('/godownstock/{id}', [GodownRollerStockController::class,'destroy']);
    Route::get('/godownverticalstock',[GodownRollerStockController::class,'VerticalStock']);
    Route::post('/godownverticalstock/{id}',[GodownRollerStockController::class,'Verticalstore']);
    Route::get('/godownverticalstock/stock/{id}',[GodownRollerStockController::class,'GodownStock']);
   
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
    Route::resource('/godownAccessory', GodownAccessoryController::class);

    Route::put('godownstockout/{id}', [StockOutController::class, 'GodownStockOutApprove']);
    
    Route::get('/getgodownstocks/{id}', [StockOutController::class, 'CheckStocks']);
    Route::post('/godownstockout', [StockoutInoviceController::class, 'store']);
    Route::put('/godownstockout/approve/{id}', [StockoutInoviceController::class, 'approve']);
    Route::get('/godownstockout', [StockoutInoviceController::class,'index']);
    Route::get('/godownstockout/{id}', [StockoutInoviceController::class,'show']);
    Route::delete('/godownstockout/{id}', [StockoutInoviceController::class,'destroy']);
    Route::get('/stockout', [StockOutController::class, 'AllStockOut']);
    Route::get('/godownout', [StockOutController::class, 'index']);
    Route::put('/godownout/{id}', [StockOutController::class, 'update']);
    Route::get('/accessoryout', [GodownAccessoryController::class, 'AllStockOut']);
    Route::get('/accessoryout/{id}', [GodownAccessoryController::class, 'GetStockOut']);
    
    // api for tansfer the stock
    Route::get('/gettranferstocks/{id}', [GodownRollerStockController::class, 'GetTransferStocks']);
    Route::post('/godowns/transfergatepass', [GatePassController::class, 'StoreTransferGatePass']);
    Route::get('/godowns/transferstocks', [GodownRollerStockController::class, 'GetTransferedStock']);

    // api for accessory trsfer
    Route::get('/gettranferaccessory/{id}', [GodownAccessoryController::class, 'GetTransferAccessory']);
    Route::post('/godowns/transfer/accessorygatepass', [GatePassController::class, 'StoreTransferAccessory']);
    
    Route::get('/sales', [StockOutController::class, 'Sales']);
    Route::get('/stockin', [StocksInController::class, 'CountStockIn']);
    // Route::get('/StockOutDash', [StockOutController::class, 'StockOutDash']);
    Route::get('stockOuttoday', [StockoutInoviceController::class, 'stockOuttoday']);
    Route::get('/barData', [ProductController::class, 'BarGraphData']);
});
