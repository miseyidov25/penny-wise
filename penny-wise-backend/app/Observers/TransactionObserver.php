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
    $goal = Goal::where('wallet_id', $walletId)->first();

    if ($goal) {
        $sum = $goal->wallet->transactions()->sum('amount');

        // Cap current_amount so it does not exceed target_amount
        $goal->current_amount = min($sum, $goal->target_amount);

        // Calculate progress percentage capped at 100%
        $goal->progress_percentage = min(
            round(($goal->current_amount / $goal->target_amount) * 100, 2),
            100
        );

        // Mark as completed if current_amount >= target_amount
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
