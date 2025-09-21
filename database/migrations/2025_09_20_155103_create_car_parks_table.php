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
        Schema::create('car_parks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name')->unique();
            $table->unsignedInteger('capacity');
            $table->decimal('default_weekday_price', 8, 2);
            $table->decimal('default_weekend_price', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_parks');
    }
};
