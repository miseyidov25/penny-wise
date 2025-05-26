<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('recurring_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
        $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
        $table->decimal('amount', 12, 2);
        $table->enum('type', ['income', 'expense']);
        $table->string('description')->nullable();
        $table->enum('interval', ['daily', 'weekly', 'monthly', 'yearly']);
        $table->date('start_date');
        $table->date('end_date')->nullable();
        $table->date('next_run');
        $table->timestamps();

        // Add additional indexes if truly needed for performance
        $table->index(['user_id', 'next_run']); // This one makes sense for scheduling
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
