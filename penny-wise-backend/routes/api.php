<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\RecurringTransactionController;
use App\Http\Controllers\GoalController;
use App\Models\User;

Route::middleware('auth:sanctum')->group(function () {
    // 👤 User routes
    Route::get('/user', [UserController::class, 'getUser']);
    Route::put('/user', [UserController::class, 'updateProfile']);
    Route::delete('/user', [UserController::class, 'deleteAccount'])->name('user.delete');
    Route::get('/users', fn () => response()->json(User::all()))->name('users.get');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('admin');

    // 💱 Currency conversion
    Route::get('/convert-currency', [CurrencyController::class, 'convertCurrency']);

    // 💼 Wallet routes
    Route::apiResource('wallets', WalletController::class)->except(['show']);
    Route::get('/wallets/{wallet}', [WalletController::class, 'show']);

    // 🗂️ Category routes
    Route::apiResource('categories', CategoryController::class)->except(['create', 'edit']);

    // 📤 Import/Export (must come BEFORE transactions resource)
    Route::get('/transactions/export', [TransactionController::class, 'export']);
    Route::post('/transactions/import', [TransactionController::class, 'import']);

    // 🔁 Recurring Transaction routes
    Route::get('/recurring-transactions', [RecurringTransactionController::class, 'index']);
    Route::post('/recurring-transactions', [RecurringTransactionController::class, 'store']);

    // 🎯 Goal routes
    Route::apiResource('goals', GoalController::class)->except(['create', 'edit']);

    // 💳 Transaction routes
    Route::apiResource('transactions', TransactionController::class)->except(['show']);
});

Route::get('/users', function () {
    return response()->json(User::all());
})->name('users.get');


    // Route to delete a user by ID
Route::delete('/users/{user}', function (User $user) {
    // Delete the user
    $user->delete();

    $users = User::all();

    return response()->json([
        'message' => 'User deleted successfully.',
        'users' => $users
    ], 200);
})->name('users.delete');

Route::get('/convert-currency', [CurrencyController::class, 'convertCurrency']);

Route::get('/debug-env', function () {
    return env('FRONTEND_URLS');
});