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
        Schema::create('option_leave_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('leave_type_code', 255)->nullable();
            $table->string('leave_type', 255)->nullable();
            $table->integer('sorting')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_locker_statuses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('locker_status', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_machine', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('department_id')->nullable();
            $table->string('machine', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->unique(['machine'], 'model');
            $table->index(['department_id'], 'department_id');
            // FK option_machine_ibfk_1 (department_id -> pivot_dept_cate_branches):
            // forward dependency — appended at the end of migration 100824.
        });
        Schema::create('option_machine_accessories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('machine_id')->nullable();
            $table->string('accessory', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['machine_id'], 'model_id');
            $table->index(['accessory'], 'accessory');
            $table->foreign('machine_id', 'option_machine_accessories_ibfk_1')->references('id')->on('option_machine')->restrictOnDelete()->restrictOnUpdate();
        });
        Schema::create('option_marital_statuses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('marital_status', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_races', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('race', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_relationships', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('relationship', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_restday_groups', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('group', 255)->nullable()->comment('saturday restday group');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_religions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('religion', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['religion'], 'religion');
        });
        Schema::create('option_sales_by', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('sales_by', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_sales_delivery_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('delivery_type', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_sales_get_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('get_item', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_sales_order_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('order_type', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_statuses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('status', 255);
            $table->char('code', 3)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['status'], 'category');
        });
        Schema::create('option_tax_exemption_percentages', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('tax_exemption_percentage')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_taxes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('tax', 255)->nullable();
            $table->string('value', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_tcms', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('leave_short', 255)->nullable();
            $table->string('leave', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_uom', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('uom', 255)->nullable()->comment('unit of measurement');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_vehicle_categories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('category', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated')->nullable();
            $table->primary('id');
        });
        Schema::create('option_vehicles', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('vehicle_category_id')->nullable();
            $table->string('vehicle', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['vehicle_category_id'], 'vehicle_category_id');
            $table->foreign('vehicle_category_id', 'option_vehicles_ibfk_1')->references('id')->on('option_vehicle_categories')->restrictOnDelete()->restrictOnUpdate();
        });
        Schema::create('option_violations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('violation', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_week_dates', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('week', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_working_hours', function (Blueprint $table) {
            $table->integer('id', true);
            $table->time('time_start_am')->nullable();
            $table->time('time_end_am')->nullable();
            $table->time('time_start_pm')->nullable();
            $table->time('time_end_pm')->nullable();
            $table->date('effective_date_start')->nullable();
            $table->date('effective_date_end')->nullable();
            $table->year('year')->nullable();
            $table->tinyInteger('category')->nullable()->comment('id for option working');
            $table->integer('group')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_yes_no', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('label', 255)->nullable();
            $table->tinyInteger('value')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['value'], 'value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // children first — option_machine_accessories FK -> option_machine,
        // option_vehicles FK -> option_vehicle_categories
        Schema::dropIfExists('option_yes_no');
        Schema::dropIfExists('option_working_hours');
        Schema::dropIfExists('option_week_dates');
        Schema::dropIfExists('option_violations');
        Schema::dropIfExists('option_vehicles');
        Schema::dropIfExists('option_vehicle_categories');
        Schema::dropIfExists('option_uom');
        Schema::dropIfExists('option_tcms');
        Schema::dropIfExists('option_tax_exemption_percentages');
        Schema::dropIfExists('option_taxes');
        Schema::dropIfExists('option_statuses');
        Schema::dropIfExists('option_sales_order_types');
        Schema::dropIfExists('option_sales_get_items');
        Schema::dropIfExists('option_sales_delivery_types');
        Schema::dropIfExists('option_sales_by');
        Schema::dropIfExists('option_religions');
        Schema::dropIfExists('option_restday_groups');
        Schema::dropIfExists('option_relationships');
        Schema::dropIfExists('option_races');
        Schema::dropIfExists('option_marital_statuses');
        Schema::dropIfExists('option_machine_accessories');
        Schema::dropIfExists('option_machine');
        Schema::dropIfExists('option_locker_statuses');
        Schema::dropIfExists('option_leave_types');
    }
};
