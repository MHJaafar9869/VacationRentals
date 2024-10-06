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
            //
            $table->date('start_date')->nullable()->after('payment_method'); // Adding start_date column
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
        });
    }
};
