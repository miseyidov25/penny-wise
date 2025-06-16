<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() 
    {
        if (!Schema::hasColumn('recurring_transactions', 'category_id')) {
            Schema::table('recurring_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->after('wallet_id');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
