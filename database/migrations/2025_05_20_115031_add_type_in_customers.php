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
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('type', ['online', 'offline'])->default('online')->after('name');
            $table->string('email')->nullable()->change();
            $table->string('no_wa')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('email')->nullable(false)->change();
            $table->string('no_wa')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
