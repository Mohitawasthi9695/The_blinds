<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\OldStockController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiverController;
use App\Http\Controllers\StockInvoiceController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockoutInoviceController;
use App\Http\Controllers\StocksInController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OperatorMiddleware;
use App\Http\Middleware\SupervisorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('forgot-password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth:sanctum')->post('/change-password', [ForgotPasswordController::class, 'changepassword'])->name('change-password');

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::Resource('/admin/users', UserController::class);
    Route::Resource('/admin/bank', BankController::class);
});
Route::middleware(['auth:sanctum', SupervisorMiddleware::class])->group(function () {
    Route::post('/stockout/approved/{id}', [StocksInController::class, 'approveStockOut']);
    Route::get('/admin/allstockout', [StockoutInoviceController::class, 'AllStockOut']);
    Route::Resource('admin/stockout', StockoutInoviceController::class);
});


Route::middleware(['auth:sanctum', OperatorMiddleware::class])->group(function () {
    Route::Resource('/supplier', SupplierController::class);
    Route::Resource('/receiver', ReceiverController::class);
    Route::Resource('/customers', CustomerController::class);
    Route::get('/data', [SupplierController::class, 'supplierStocks']);
    Route::get('/recent-suppliers', [SupplierController::class, 'RecentSuppliers']);
    Route::Resource('/products', ProductController::class);
    Route::get('/operator/bank', [BankController::class, 'index']);
    Route::Resource('/stockin/invoice', StockInvoiceController::class);
    Route::get('/stockout/invoiceno', [StockoutInoviceController::class, 'invoice_no']);
    Route::Resource('/stocks', StocksInController::class);
    Route::Resource('/oldstocks', OldStockController::class);
    Route::get('/checkstocks/{product_id}', [ProductController::class, 'CheckStocks']);
    Route::get('/available', [ProductController::class, 'AvailableProducts']);
    Route::Resource('stockout', StockoutInoviceController::class);

    Route::get('/sales', [StockOutController::class, 'Sales']);
    Route::get('/StockOutDash', [StockOutController::class, 'StockOutDash']);

    Route::get('/allstockout', [StockoutInoviceController::class, 'AllStockOut']);

    Route::get('stockOuttoday', [StockoutInoviceController::class, 'stockOuttoday']);
    Route::get('/barData', [ProductController::class, 'BarGraphData']);
    Route::post('/stocks/import-csv', [StocksInController::class, 'storeFromCsv']); 
});
