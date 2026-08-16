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
        Schema::create('ci_categories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('category', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('ci_category_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('ci_category_id')->nullable();
            $table->longText('description')->nullable();
            $table->float('point')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['ci_category_id'], 'ci_category_items_ibfk_1');
            $table->foreign('ci_category_id', 'ci_category_items_ibfk_1')->references('id')->on('ci_categories')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // children first — ci_category_items has an FK to ci_categories
        Schema::dropIfExists('ci_category_items');
        Schema::dropIfExists('ci_categories');
    }
};
