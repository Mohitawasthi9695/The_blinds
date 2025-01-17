<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GodownController;
use App\Http\Controllers\OldStockController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiverController;
use App\Http\Controllers\StockInvoiceController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockoutInoviceController;
use App\Http\Controllers\StocksInController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('forgot-password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/change-password', [ForgotPasswordController::class, 'changepassword']);
    Route::Resource('/supplier', SupplierController::class);
    Route::Resource('/receiver', ReceiverController::class);
    Route::get('/sub_supervisor', [UserController::class, 'Sub_supervisor']);
    Route::Resource('/customers', CustomerController::class);
    Route::get('/data', [SupplierController::class, 'supplierStocks']);
    Route::get('/recent-suppliers', [SupplierController::class, 'RecentSuppliers']);
    Route::Resource('/products', ProductController::class);
    Route::Resource('/stockin/invoice', StockInvoiceController::class);
    Route::get('/stockout/invoiceno', [StockoutInoviceController::class, 'invoice_no']);
    Route::get('/godown/invoiceno', [GodownController::class, 'invoice_no']);
    Route::Resource('/stocks', StocksInController::class);
    Route::Resource('/admin/users', UserController::class);
    Route::Resource('/oldstocks', OldStockController::class);
    Route::Resource('/godown', GodownController::class);
    Route::get('/sub_supervisor/godown/{id}', [GodownController::class, 'Sub_supervisorStock']);
    Route::get('/godown/stock/{id}', [GodownController::class,'godownStock']);
    Route::get('/checkstocks/{product_id}', [ProductController::class, 'CheckStocks']);

    Route::get('/available', [ProductController::class, 'AvailableProducts']);
    Route::get('/godownproducts',[GodownController::class, 'GetStockProducts']);
    Route::get('/godowncheckout/{id}',[GodownController::class, 'GetStockCheckout']);
    Route::post('godownstockout', [GodownController::class,'GodownStockOut']);

    Route::put('godownstockout/{id}', [StockOutController::class,'GodownStockOutApprove']);

    Route::Resource('stockout', StockoutInoviceController::class);
    Route::post('/godown/approved/{id}', [GodownController::class, 'GodownStockStatus']);
    Route::get('/admin/allstockout', [StockoutInoviceController::class, 'AllStockOut']);
    Route::Resource('admin/stockout', StockoutInoviceController::class);
    Route::get('/sales', [StockOutController::class, 'Sales']);
    Route::get('/StockOutDash', [StockOutController::class, 'StockOutDash']);
    Route::get('/allstockout', [StockoutInoviceController::class, 'AllStockOut']);
    Route::get('stockOuttoday', [StockoutInoviceController::class, 'stockOuttoday']);
    Route::get('/barData', [ProductController::class, 'BarGraphData']);
    Route::post('/stocks/import-csv', [StocksInController::class, 'storeFromCsv']);

});
