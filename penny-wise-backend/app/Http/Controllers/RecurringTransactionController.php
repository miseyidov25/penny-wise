<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use App\Services\CurrencyConversionService;
use App\Models\Transaction;


class RecurringTransactionController extends Controller
{
    protected $currencyService;

    // ✅ Constructor-based dependency injection
    public function __construct(CurrencyConversionService $currencyService)
    {
        $this->currencyService = $currencyService;
    }


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
            'interval' => $transaction->interval,
            'next_run' => $transaction->next_run,
            'start_date' => $transaction->start_date, // ✅ Added
            'end_date' => $transaction->end_date,
            'category_name' => $transaction->category?->name,
            'type' => $convertedAmount < 0 ? 'expense' : 'income',
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
        'start_date' => 'required|date|before_or_equal:next_run',
        'end_date' => 'nullable|date|after_or_equal:next_run',
    ]);

    $wallet = Wallet::where('id', $validated['wallet_id'])
                    ->where('user_id', Auth::id())
                    ->first();

    if (!$wallet) {
        return response()->json(['error' => 'Unauthorized: Wallet does not belong to user'], 403);
    }

    $category = Category::firstOrCreate([
        'name' => $validated['category_name'],
        'user_id' => Auth::id(),
    ]);

    $convertedAmount = $this->currencyService->convertForWallet('EUR', $wallet->currency, $validated['amount']);

    $type = $convertedAmount < 0 ? 'expense' : 'income';

    // Update wallet balance immediately
    if ($convertedAmount < 0) {
        $wallet->balance -= abs($convertedAmount);
    } else {
        $wallet->balance += $convertedAmount;
    }
    $wallet->save();

    // Create recurring transaction
    $recurring = RecurringTransaction::create([
        'user_id' => Auth::id(),
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => $convertedAmount,
        'description' => $validated['description'] ?? null,
        'interval' => $validated['interval'],
        'next_run' => $validated['next_run'],
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'] ?? null,
        'currency' => $wallet->currency,
        'type' => $type,
    ]);

    // Create a corresponding Transaction for the first occurrence of the recurring transaction
    Transaction::create([
        'user_id' => Auth::id(),
        'wallet_id' => $wallet->id,
        'category_id' => $category->id,
        'amount' => $convertedAmount,
        'description' => $validated['description'] ?? null,
        'date' => $validated['next_run'], // first payment date
        'currency' => $wallet->currency,
    ]);

    $wallet->load(['recurringTransactions.category']);

    $recurringTransactions = $wallet->recurringTransactions->map(function ($transaction) {
        return [
            'id' => $transaction->id,
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'interval' => $transaction->interval,
            'next_run' => $transaction->next_run,
            'start_date' => $transaction->start_date,
            'end_date' => $transaction->end_date,
            'category_name' => $transaction->category?->name,
            'type' => $transaction->type,
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
