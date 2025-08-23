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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_type')->nullable()->after('status');
            $table->datetime('transaction_time')->nullable()->after('payment_type');
            $table->datetime('expiry_time')->nullable()->after('transaction_time');
            $table->decimal('amount', 15, 2)->nullable()->after('expiry_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'transaction_time', 'expiry_time', 'amount']);
        });
    }
};
