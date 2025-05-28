<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;

class RecurringTransactionController extends Controller
{
    public function index()
{
    $recurringTransactions = RecurringTransaction::with(['category', 'wallet'])
        ->where('user_id', Auth::id())
        ->get();

    $primaryCurrency = 'EUR'; // You can later fetch this from user profile

    return response()->json($recurringTransactions->map(function ($transaction) use ($primaryCurrency) {
        $convertedAmount = $this->currencyService->convertForWallet(
            $transaction->currency,
            $primaryCurrency,
            $transaction->amount
        );

        return [
            'id' => $transaction->id,
            'wallet_id' => $transaction->wallet_id,
            'amount' => number_format($convertedAmount, 2),
            'currency' => $primaryCurrency,
            'description' => $transaction->description,
            'next_run' => $transaction->next_run,
            'interval' => $transaction->interval,
            'end_date' => $transaction->end_date,
            'category_name' => $transaction->category?->name,
        ];
    }));
}

    public function store(Request $request)
{
    $validated = $request->validate([
        'wallet_id' => 'required|exists:wallets,id',
        'category_name' => 'required|string|max:255',
        'amount' => [
            'required',
            'numeric',
            'min:-99999999',
            'max:99999999',
            function ($attribute, $value, $fail) {
                if ($value == 0) {
                    $fail('The amount cannot be zero.');
                }
            },
        ],
        'description' => 'nullable|string',
        'interval' => 'required|in:daily,weekly,monthly,yearly',
        'next_run' => 'required|date',
        'end_date' => 'nullable|date|after_or_equal:next_run',
    ]);

    // Check wallet ownership
    $wallet = Wallet::where('id', $validated['wallet_id'])
                    ->where('user_id', Auth::id())
                    ->first();

    if (!$wallet) {
        return response()->json(['error' => 'Unauthorized: Wallet does not belong to user'], 403);
    }

    // Find or create category by name
    $category = Category::firstOrCreate([
        'name' => $validated['category_name'],
        'user_id' => Auth::id(),
    ]);

    dd($category);

    // Convert amount to wallet currency
    $convertedAmount = $this->currencyService->convertForWallet('EUR', $wallet->currency, $validated['amount']);

    // Update wallet balance immediately (same as transaction)
    if ($convertedAmount < 0) {
        $wallet->balance -= abs($convertedAmount);
    } else {
        $wallet->balance += $convertedAmount;
    }

    $wallet->save();

    // Create recurring transaction record
    $recurring = RecurringTransaction::create([
        'user_id' => Auth::id(),
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => $convertedAmount,
        'description' => $validated['description'] ?? null,
        'interval' => $validated['interval'],
        'next_run' => $validated['next_run'],
        'end_date' => $validated['end_date'] ?? null,
        'currency' => $wallet->currency, // You can add currency field to recurring transactions
    ]);

    // Load recurring transactions with category names
    $wallet->load(['recurringTransactions.category']);

    $recurringTransactions = $wallet->recurringTransactions->map(function ($transaction) {
        return [
            'id' => $transaction->id,
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'interval' => $transaction->interval,
            'next_run' => $transaction->next_run,
            'end_date' => $transaction->end_date,
            'category_name' => $transaction->category?->name,
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Recurring transaction created successfully.',
        'wallet' => [
            'id' => $wallet->id,
            'name' => $wallet->name,
            'balance' => $wallet->balance,
            'currency' => $wallet->currency,
            'recurring_transactions' => $recurringTransactions,
        ],
    ], 201);
}



}
