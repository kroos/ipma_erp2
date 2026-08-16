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
        Schema::create('quot_banks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('bank')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_dealers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('dealer')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_delivery_dates', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('delivery_date_period', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_exclusions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('exclusion')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_item_attributes', function (Blueprint $table) {
            $table->integer('id', true)->comment(' ');
            $table->string('attribute', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('item')->nullable();
            $table->longText('info')->nullable();
            $table->decimal('price', 65, 2)->nullable();
            $table->tinyInteger('active')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_remarks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('quot_remarks')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_uom', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('uom', 255)->nullable()->comment('unit of measurement');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_warranties', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('warranty')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        // quot_quotations — dump has no ibfk_2; FK numbering kept verbatim
        Schema::create('quot_quotations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->date('date')->nullable();
            $table->string('subject', 255)->nullable();
            $table->integer('customer_id')->nullable();
            $table->string('attn', 255)->nullable();
            $table->integer('currency_id')->nullable();
            $table->longText('description')->nullable();
            $table->integer('tax_id')->nullable();
            $table->decimal('tax_value', 65, 2)->nullable();
            $table->decimal('discount', 65, 2)->nullable();
            $table->integer('mutual')->nullable();
            $table->string('from', 255)->nullable();
            $table->string('to', 255)->nullable();
            $table->integer('period_id')->nullable();
            $table->string('validity', 255)->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('grandamount', 255)->nullable();
            $table->decimal('dealer_price', 65, 2)->nullable();
            $table->tinyInteger('budget_quot')->nullable();
            $table->integer('active')->nullable();
            $table->longText('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['customer_id'], 'customer_id');
            $table->index(['currency_id'], 'currency_id');
            $table->index(['tax_id'], 'tax_id');
            $table->index(['period_id'], 'period_id');
            $table->index(['bank_id'], 'bank_id');
            $table->foreign('staff_id', 'quot_quotations_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('currency_id', 'quot_quotations_ibfk_3')->references('id')->on('option_currencies')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('tax_id', 'quot_quotations_ibfk_4')->references('id')->on('option_taxes')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('period_id', 'quot_quotations_ibfk_5')->references('id')->on('quot_delivery_dates')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('bank_id', 'quot_quotations_ibfk_6')->references('id')->on('quot_banks')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('customer_id', 'quot_quotations_ibfk_7')->references('id')->on('customers')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('quot_quotation_dealers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quot_id')->nullable();
            $table->integer('dealer_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['quot_id'], 'quot_id');
            $table->index(['dealer_id'], 'dealer_id');
            $table->foreign('quot_id', 'quot_quotation_dealers_ibfk_1')->references('id')->on('quot_quotations')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('dealer_id', 'quot_quotation_dealers_ibfk_2')->references('id')->on('quot_dealers')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('quot_quotation_exclusions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quot_id')->nullable();
            $table->integer('exclusion_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['quot_id'], 'quot_id');
            $table->index(['exclusion_id'], 'exclusion_id');
            $table->foreign('quot_id', 'quot_quotation_exclusions_ibfk_1')->references('id')->on('quot_quotations')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('exclusion_id', 'quot_quotation_exclusions_ibfk_2')->references('id')->on('quot_exclusions')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('quot_quotation_remarks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quot_id')->nullable();
            $table->integer('remark_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['quot_id'], 'quot_id');
            $table->index(['remark_id'], 'remark_id');
            $table->foreign('quot_id', 'quot_quotation_remarks_ibfk_1')->references('id')->on('quot_quotations')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('remark_id', 'quot_quotation_remarks_ibfk_2')->references('id')->on('quot_remarks')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('quot_quotation_revisions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quot_id')->nullable();
            $table->string('revision', 255)->nullable();
            $table->string('revision_file', 255)->nullable();
            $table->longText('description')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('quot_quotation_sections', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quot_id')->nullable();
            $table->longText('section')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['quot_id'], 'quot_id');
            $table->foreign('quot_id', 'quot_quotation_sections_ibfk_1')->references('id')->on('quot_quotations')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('quot_quotation_section_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('section_id')->nullable();
            $table->integer('item_id')->nullable();
            $table->decimal('price_unit', 65, 2)->nullable();
            $table->integer('uom_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('tax_id')->nullable();
            $table->decimal('tax_value', 65, 2)->nullable();
            $table->longText('description')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['section_id'], 'section_id');
            $table->index(['item_id'], 'item_id');
            $table->index(['uom_id'], 'uom_id');
            $table->index(['tax_id'], 'tax_id');
            $table->foreign('section_id', 'quot_quotation_section_items_ibfk_1')->references('id')->on('quot_quotation_sections')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('item_id', 'quot_quotation_section_items_ibfk_2')->references('id')->on('quot_items')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('uom_id', 'quot_quotation_section_items_ibfk_3')->references('id')->on('quot_uom')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('tax_id', 'quot_quotation_section_items_ibfk_4')->references('id')->on('option_taxes')->restrictOnDelete()->restrictOnUpdate();
        });

        // dump has no ibfk_1 — FK numbering kept verbatim
        Schema::create('quot_quotation_section_item_attributes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('item_id')->nullable();
            $table->integer('attribute_id')->nullable();
            $table->longText('description_attribute')->nullable();
            $table->string('image', 255)->nullable();
            $table->longText('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['attribute_id'], 'attribute_id');
            $table->index(['item_id'], 'item_id');
            $table->foreign('attribute_id', 'quot_quotation_section_item_attributes_ibfk_2')->references('id')->on('quot_item_attributes')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('item_id', 'quot_quotation_section_item_attributes_ibfk_3')->references('id')->on('quot_quotation_section_items')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('quot_quotation_term_of_payments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quot_id')->nullable();
            $table->longText('term_of_payment')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['quot_id'], 'quot_id');
            $table->foreign('quot_id', 'quot_quotation_term_of_payments_ibfk_1')->references('id')->on('quot_quotations')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('quot_quotation_warranties', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('quot_id')->nullable();
            $table->integer('warranty_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['quot_id'], 'quot_id');
            $table->index(['warranty_id'], 'warranty_id');
            $table->foreign('quot_id', 'quot_quotation_warranties_ibfk_1')->references('id')->on('quot_quotations')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('warranty_id', 'quot_quotation_warranties_ibfk_2')->references('id')->on('quot_warranties')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quot_quotation_warranties');
        Schema::dropIfExists('quot_quotation_term_of_payments');
        Schema::dropIfExists('quot_quotation_section_item_attributes');
        Schema::dropIfExists('quot_quotation_section_items');
        Schema::dropIfExists('quot_quotation_sections');
        Schema::dropIfExists('quot_quotation_revisions');
        Schema::dropIfExists('quot_quotation_remarks');
        Schema::dropIfExists('quot_quotation_exclusions');
        Schema::dropIfExists('quot_quotation_dealers');
        Schema::dropIfExists('quot_quotations');
        Schema::dropIfExists('quot_warranties');
        Schema::dropIfExists('quot_uom');
        Schema::dropIfExists('quot_remarks');
        Schema::dropIfExists('quot_items');
        Schema::dropIfExists('quot_item_attributes');
        Schema::dropIfExists('quot_exclusions');
        Schema::dropIfExists('quot_delivery_dates');
        Schema::dropIfExists('quot_dealers');
        Schema::dropIfExists('quot_banks');
    }
};
