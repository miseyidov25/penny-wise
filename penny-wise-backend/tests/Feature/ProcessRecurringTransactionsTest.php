<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\RecurringTransaction;
use Carbon\Carbon;

class ProcessRecurringTransactionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_processes_due_recurring_transactions_and_updates_next_run()
    {
        // Setup
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'currency' => 'EUR']);
        $category = Category::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringTransaction::create([
            'user_id'     => $user->id,
            'wallet_id'   => $wallet->id,
            'category_id' => $category->id,
            'amount'      => 25.00,
            'interval'    => 'daily',
            'type'        => 'expense',
            'description' => 'Daily coffee',
            'next_run'    => Carbon::yesterday(),
            'end_date'    => Carbon::now()->addWeek(),
        ]);

        // Execute the command
        $this->artisan('your:command-name') // Replace with actual command name
            ->expectsOutput('Recurring transactions processed.')
            ->assertExitCode(0);

        // Assert transaction was created
        $this->assertDatabaseHas('transactions', [
            'user_id'     => $user->id,
            'wallet_id'   => $wallet->id,
            'category_id' => $category->id,
            'amount'      => 25.00,
            'currency'    => 'EUR',
            'type'        => 'expense',
            'description' => 'Daily coffee',
            'date'        => Carbon::today()->toDateString(),
        ]);

        // Assert next_run updated
        $recurring->refresh();
        $this->assertEquals(Carbon::today()->addDay()->toDateString(), $recurring->next_run->toDateString());
    }

    /** @test */
    public function it_does_not_process_past_end_date()
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['user_id' => $user->id]);

        $recurring = RecurringTransaction::create([
            'user_id'     => $user->id,
            'wallet_id'   => $wallet->id,
            'category_id' => $category->id,
            'amount'      => 10.00,
            'interval'    => 'daily',
            'type'        => 'expense',
            'description' => 'Old sub',
            'next_run'    => Carbon::yesterday(),
            'end_date'    => Carbon::yesterday(),
        ]);

        $this->artisan('recurring:run')
            ->expectsOutput("Skipped updating recurring ID {$recurring->id} — past end date")
            ->expectsOutput('Recurring transactions processed.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('transactions', [
            'description' => 'Old sub'
        ]);
    }
}

