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
        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('properties', function (Blueprint $table) {
        $table->dropColumn(['latitude', 'longitude']);
    });
}
};
