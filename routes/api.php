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

    Route::resource('/products/category', ProductCategoryController::class);
    Route::resource('/products', ProductController::class);
    Route::post('/product/import-csv', [ProductController::class, 'ProductCsv']);

    Route::get('/productshadeno/{category_id}', [ProductController::class, 'ProductShadeNo']);

    Route::resource('/stockin/invoice', StockInvoiceController::class);

    Route::get('/stockout/invoiceno', [StockoutInoviceController::class, 'invoice_no']);

    Route::resource('/stocks', StocksInController::class);
    Route::post('/stocks/import-csv', [StocksInController::class, 'storeFromCsv']);
    Route::get('/category/rollerstock', [StocksInController::class, 'RollerStocks']);
    Route::get('/category/woodenstock', [StocksInController::class, 'WoodenStocks']);
    Route::get('/category/verticalstock', [StocksInController::class, 'VerticalStocks']);
    Route::get('/category/honeycombstock', [StocksInController::class, 'HoneyCombStocks']);

    Route::get('/gatepassno', [GatePassController::class, 'GatePassNo']);

    Route::get('/getstocks/{id}', [StocksInController::class, 'CheckStocks']);
    
    Route::get('/godowns/getStockgatepass', [GatePassController::class, 'GetAllStockGatePass']);
    Route::get('/godowns/getStockgatepass/{id}', [GatePassController::class, 'GetStockGatePass']);

    Route::resource('/godownrollerstock', GodownRollerStockController::class);
    Route::resource('/godownwoodenstock', GodownWoodenStockController::class);
    Route::resource('/godownverticalstock', GodownVerticalStockController::class);
    Route::resource('/godownhoneycombstock', GodownHoneyCombStockController::class);

   
    Route::get('/accessory/getStockgatepass', [GatePassController::class, 'GetAllAccessoryGatePass']);
    Route::get('/accessory/getStockgatepass/{id}', [GatePassController::class, 'GetAccessoryGatePass']);
    Route::post('/godowns/gatepass', [GatePassController::class, 'StoreStockGatePass']);
    Route::put('/godowns/gatepass/{id}', [GatePassController::class, 'UpdateGatePass']);
    Route::delete('/godowns/gatepass/{id}', [GatePassController::class, 'DeleteGatePass']);
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

    Route::get('/getgodownstocks/{id}', [GodownRollerStockController::class, 'CheckStocks']);
    Route::get('/Cproducts', [GodownRollerStockController::class, 'GetStockProducts']);
    Route::get('/godowncheckout/{id}', [GodownRollerStockController::class, 'GetStockCheckout']);
    Route::post('godownstockout', [GodownRollerStockController::class, 'GodownStockOut']);
    Route::put('godownstockout/{id}', [StockOutController::class, 'GodownStockOutApprove']);

    Route::resource('stockout', StockoutInoviceController::class);
    Route::get('/admin/allstockout', [StockoutInoviceController::class, 'AllStockOut']);
    Route::resource('admin/stocksout', StockoutInoviceController::class);
    Route::get('/sales', [StockOutController::class, 'Sales']);
    Route::get('/StockOutDash', [StockOutController::class, 'StockOutDash']);
    Route::get('/allstockout', [StockoutInoviceController::class, 'AllStockOut']);
    Route::get('stockOuttoday', [StockoutInoviceController::class, 'stockOuttoday']);
    Route::get('/barData', [ProductController::class, 'BarGraphData']);
});
