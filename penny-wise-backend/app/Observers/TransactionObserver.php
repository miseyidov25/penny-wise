<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\Goal;

class TransactionObserver
{
    public function created(Transaction $transaction)
    {
        $this->updateGoalProgress($transaction->wallet_id);
    }

    public function updated(Transaction $transaction)
    {
        $this->updateGoalProgress($transaction->wallet_id);
    }

    public function deleted(Transaction $transaction)
    {
        $this->updateGoalProgress($transaction->wallet_id);
    }

    protected function updateGoalProgress($walletId)
{
    $goal = Goal::where('wallet_id', $walletId)->where('is_completed', false)->first();

    if ($goal) {
        $wallet = $goal->wallet;

        // Sum all transactions of this wallet
        $sum = $wallet->transactions()->sum('amount');

        $goal->current_amount = min($sum, $goal->target_amount);
        $goal->progress_percentage = min(round(($goal->current_amount / $goal->target_amount) * 100, 2), 100);
        $goal->is_completed = $goal->current_amount >= $goal->target_amount;
        $goal->save();
    }
}




    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
