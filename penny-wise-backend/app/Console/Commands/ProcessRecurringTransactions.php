<?php

namespace App\Console\Commands;

use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-recurring-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processes all due recurring transactions.';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $today = Carbon::today();

    $recurrings = RecurringTransaction::whereDate('next_run', '<=', $today)->get();

    foreach ($recurrings as $recurring) {
        // Create actual transaction
        Transaction::create([
            'user_id'     => $recurring->user_id,
            'wallet_id'   => $recurring->wallet_id,
            'category_id' => $recurring->category_id,
            'amount'      => $recurring->amount,
            'currency'    => $recurring->wallet->currency ?? 'USD',
            'type'        => $recurring->type,
            'description' => $recurring->description,
            'date'        => $today,
        ]);

        // Calculate next run date
        $nextRun = Carbon::parse($recurring->next_run)->copy();
        match ($recurring->interval) {
            'daily'   => $nextRun->addDay(),
            'weekly'  => $nextRun->addWeek(),
            'monthly' => $nextRun->addMonth(),
            'yearly'  => $nextRun->addYear(),
        };

        // Respect end date
        if ($recurring->end_date && $nextRun->gt(Carbon::parse($recurring->end_date))) {
            $this->warn("Skipped updating recurring ID {$recurring->id} — past end date");
            continue;
        }

        $recurring->update(['next_run' => $nextRun]);
    }

    $this->info('Recurring transactions processed.');
}

}
