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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('guest_id');
            $table->unsignedBigInteger('booking_id');
            $table->mediumText('message');

            $table->foreign('owner_id')->references('id')->on('owners')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};