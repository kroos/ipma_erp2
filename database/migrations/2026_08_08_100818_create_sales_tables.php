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
        Schema::create('sales', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->integer('sales_by_id')->nullable();
            $table->integer('no')->nullable();
            // dump: year — no Laravel equivalent, stored as smallint unsigned
            $table->unsignedSmallInteger('year')->nullable();
            $table->date('date_order')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('quotation_no', 255)->nullable();
            $table->date('delivery_at')->nullable();
            $table->integer('spec_req')->nullable();
            $table->string('special_request', 255)->nullable();
            $table->tinyInteger('stock')->nullable();
            $table->integer('deliveryby_id')->nullable();
            $table->longText('special_delivery_instruction')->nullable();
            $table->integer('sales_type_id')->nullable();
            $table->tinyInteger('urgency')->nullable();
            $table->string('po_number', 255)->nullable();
            $table->tinyInteger('confirm')->nullable();
            $table->date('confirm_date')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['sales_by_id'], 'sales_by_id');
            $table->index(['customer_id'], 'customer_id');
            $table->index(['sales_type_id'], 'sales_type_id');
            // dump: INDEX deliveryby_id(deliveryby_id) — no FK for deliveryby_id in the dump
            $table->index(['deliveryby_id'], 'deliveryby_id');
            $table->foreign('staff_id', 'sales_ibfk_1')->references('id')->on('staffs')->nullOnDelete()->restrictOnUpdate();
            $table->foreign('sales_by_id', 'sales_ibfk_2')->references('id')->on('option_sales_by')->nullOnDelete()->restrictOnUpdate();
            $table->foreign('customer_id', 'sales_ibfk_3')->references('id')->on('customers')->nullOnDelete()->restrictOnUpdate();
            $table->foreign('sales_type_id', 'sales_ibfk_4')->references('id')->on('option_sales_order_types')->nullOnDelete()->restrictOnUpdate();
        });

        Schema::create('sales_amends', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sales_id')->nullable();
            $table->string('amend', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('sales_job_descriptions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sales_id')->nullable();
            $table->longText('job_description')->nullable();
            $table->float('quantity')->nullable();
            $table->integer('uom_id')->nullable();
            $table->integer('machine_id')->nullable();
            $table->integer('machine_accessory_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['sales_id'], 'sales_id');
            $table->index(['uom_id'], 'sales_job_descriptions_ibfk_2');
            $table->index(['machine_id'], 'sales_job_descriptions_ibfk_3');
            $table->index(['machine_accessory_id'], 'sales_job_descriptions_ibfk_4');
            $table->foreign('sales_id', 'sales_job_descriptions_ibfk_1')->references('id')->on('sales')->nullOnDelete()->restrictOnUpdate();
            $table->foreign('uom_id', 'sales_job_descriptions_ibfk_2')->references('id')->on('option_uom')->nullOnDelete()->restrictOnUpdate();
            $table->foreign('machine_id', 'sales_job_descriptions_ibfk_3')->references('id')->on('option_machine')->nullOnDelete()->restrictOnUpdate();
            $table->foreign('machine_accessory_id', 'sales_job_descriptions_ibfk_4')->references('id')->on('option_machine_accessories')->nullOnDelete()->restrictOnUpdate();
        });

        Schema::create('cps_main', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('no')->nullable();
            // dump: year — no Laravel equivalent, stored as smallint unsigned
            $table->unsignedSmallInteger('year')->nullable();
            $table->integer('staff_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('special_request', 255)->nullable();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // children first — sales_job_descriptions has an FK to sales
        Schema::dropIfExists('cps_main');
        Schema::dropIfExists('sales_job_descriptions');
        Schema::dropIfExists('sales_amends');
        Schema::dropIfExists('sales');
    }
};
