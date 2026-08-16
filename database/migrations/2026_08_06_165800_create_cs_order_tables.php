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
        Schema::create('cs_order_deliveries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('delivery_method', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('cs_order_item_statuses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('order_item_status', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('cs_orders', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('date')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('requester', 255)->nullable();
            $table->integer('informed_by')->nullable();
            $table->string('customer_PO_no', 255)->nullable();
            $table->integer('pic')->nullable();
            $table->longText('description')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['customer_id'], 'customer_id');
            $table->index(['informed_by'], 'informed_by');
            $table->index(['pic'], 'pic');
            // FK cs_orders_ibfk_2 (informed_by -> staffs), cs_orders_ibfk_3 (pic -> staffs),
            // cs_orders_ibfk_4 (customer_id -> customers): forward dependency —
            // appended at the end of migration 100816.
        });

        Schema::create('cs_order_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('order_id')->nullable();
            $table->longText('order_item')->nullable();
            $table->string('quantity', 255)->nullable();
            $table->string('item_additional_info', 255)->nullable();
            $table->integer('order_item_status_id')->nullable();
            $table->longText('description')->nullable()->comment('item remarks');
            $table->integer('delivery_id')->nullable();
            $table->date('delivery_date')->nullable();
            $table->longText('delivery_remarks')->nullable()->comment('delivery remarks');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['order_id'], 'order_id');
            $table->index(['order_item_status_id'], 'order_item_status_id');
            $table->index(['delivery_id'], 'delivery_id');
            $table->foreign('order_id', 'cs_order_items_ibfk_1')->references('id')->on('cs_orders')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('order_item_status_id', 'cs_order_items_ibfk_2')->references('id')->on('cs_order_item_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('delivery_id', 'cs_order_items_ibfk_3')->references('id')->on('cs_order_deliveries')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // children first — cs_order_items has FKs to cs_orders / cs_order_item_statuses / cs_order_deliveries
        Schema::dropIfExists('cs_order_items');
        Schema::dropIfExists('cs_orders');
        Schema::dropIfExists('cs_order_deliveries');
        Schema::dropIfExists('cs_order_item_statuses');
    }
};
