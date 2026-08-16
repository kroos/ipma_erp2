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
        Schema::create('ics_floatth_constant', function (Blueprint $table) {
            $table->integer('id', true);
            $table->float('overtime_constant_1', 5, 5)->nullable();
            $table->float('overtime_constant_2', 10, 5)->nullable();
            $table->integer('accomodation_rate')->nullable();
            $table->float('travel_meter_rate', 10, 5)->nullable();
            $table->float('travel_hour_rate', 10, 5)->nullable();
            $table->tinyInteger('active')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('ics_food_rates', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('food_rate', 255)->nullable();
            $table->integer('value')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ics_floatth_constant');
        Schema::dropIfExists('ics_food_rates');
    }
};
