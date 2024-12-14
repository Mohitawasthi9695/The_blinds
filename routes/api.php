<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiverController;
use App\Http\Controllers\StockInvoiceController;
use App\Http\Controllers\StocksInController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CmpMiddleware;
use App\Http\Middleware\OperatorMiddleware;
use App\Http\Middleware\SupervisorMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Constraint\Operator;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode'])->name('forgot-password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::middleware('auth:sanctum')->post('/change-password', [ForgotPasswordController::class, 'changepassword'])->name('change-password');

Route::middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::Resource('/admin/bank', BankController::class)->names('admin.bank');
    Route::Resource('/admin/users', UserController::class)->names('admin.users');
});
Route::middleware(['auth:sanctum', OperatorMiddleware::class])->group(function () {
    Route::Resource('/supplier', SupplierController::class);
    Route::Resource('/receiver', ReceiverController::class);
    Route::Resource('/product', ProductController::class);
    Route::get('/operator/bank', [BankController::class, 'index']);
    Route::Resource('/stockin/invoice', StockInvoiceController::class)->names('stockin.invoice');
    Route::Resource('/stocks', StocksInController::class);
});

