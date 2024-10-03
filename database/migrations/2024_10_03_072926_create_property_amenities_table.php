<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_amenities', function (Blueprint $table) {
            $table->unsignedBigInteger('amenity_id');
            $table->unsignedBigInteger('property_id');
            $table->primary(['amenity_id', 'property_id']);
            $table->foreign('property_id')->references(columns: 'id')->on('properties')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('amenity_id')->references(columns: 'id')->on('amenities')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_amenities');
    }
};