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
		Schema::create('ics_accommodation_rates', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('accommodation_rate', 255)->nullable();
			$table->float('value', 10, 5)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_categories', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('sr_category', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_charges', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('charge', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_machine_models', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('model', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_proceeds', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('proceed', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_status', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('sr_status', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_working_types', function (Blueprint $table) {
			$table->integer('id', true);
			$table->string('working_type', 255)->nullable();
			$table->float('value', 10, 5)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_service_reports', function (Blueprint $table) {
			$table->integer('id', true);
			$table->date('date')->nullable();
			$table->integer('staff_id')->nullable();
			$table->integer('customer_id')->nullable();
			$table->integer('category_id')->nullable();
			$table->integer('charge_id')->nullable();
			$table->integer('inform_by')->nullable();
			$table->integer('approved_by')->nullable();
			$table->integer('checked_by')->nullable();
			$table->date('checked_date')->nullable();
			$table->integer('proceed_id')->nullable();
			$table->integer('send_by')->nullable();
			$table->date('send_date')->nullable();
			$table->integer('invoice_id')->nullable();
			$table->date('invoice_date')->nullable();
			$table->longText('invoice_remarks')->nullable();
			$table->integer('updated_by')->nullable();
			$table->tinyInteger('active')->nullable();
			$table->integer('status_id')->nullable();
			$table->longText('remarks')->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['charge_id'], 'charge_id');
			$table->index(['staff_id'], 'staff_id');
			$table->index(['customer_id'], 'customer_id');
			$table->index(['approved_by'], 'approved_by');
			$table->index(['proceed_id'], 'proceed_id');
			$table->index(['inform_by'], 'inform_by');
			$table->index(['updated_by'], 'updated_by');
			$table->index(['checked_by'], 'checked_by');
			$table->index(['category_id'], 'category_id');
			$table->index(['status_id'], 'status_id');
			$table->foreign('charge_id', 'ics_service_reports_ibfk_1')->references('id')->on('ics_charges')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('staff_id', 'ics_service_reports_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('approved_by', 'ics_service_reports_ibfk_5')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('proceed_id', 'ics_service_reports_ibfk_6')->references('id')->on('ics_proceeds')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('inform_by', 'ics_service_reports_ibfk_7')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('updated_by', 'ics_service_reports_ibfk_8')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('checked_by', 'ics_service_reports_ibfk_9')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('category_id', 'ics_service_reports_ibfk_10')->references('id')->on('ics_categories')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('status_id', 'ics_service_reports_ibfk_11')->references('id')->on('ics_status')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('customer_id', 'ics_service_reports_ibfk_12')->references('id')->on('customers')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_additional_charges', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->integer('amount_id')->nullable();
			$table->string('value', 10)->nullable();
			$table->longText('description')->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->index(['amount_id'], 'amount_id');
			$table->foreign('service_report_id', 'ics_service_report_additional_charges_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('amount_id', 'ics_service_report_additional_charges_ibfk_2')->references('id')->on('option_amounts')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_attendees', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->integer('attended_by')->nullable()->comment('id staff outstation');
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->index(['attended_by'], 'attended_by');
			$table->foreign('service_report_id', 'ics_service_report_attendees_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('attended_by', 'ics_service_report_attendees_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_attendees_phone', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->text('phone_number')->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_attendees_phone_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_complaints', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->longText('complaint')->nullable();
			$table->string('complaint_by', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_complaints_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_discounts', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->integer('discount_id')->nullable();
			$table->float('value', 10, 5)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->index(['discount_id'], 'discount_id');
			$table->foreign('service_report_id', 'ics_service_report_discounts_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('discount_id', 'ics_service_report_discounts_ibfk_2')->references('id')->on('option_discount_types')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_feed_calls', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->date('date')->nullable();
			$table->string('pic', 255)->nullable();
			$table->longText('remarks')->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_feed_calls_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_feed_items', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->string('item', 255)->nullable();
			$table->string('quantity', 255)->nullable();
			$table->string('item_action', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_feed_items_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_feed_problems', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->longText('problem')->nullable();
			$table->longText('solution')->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_feed_problems_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_feed_requests', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->string('request', 255)->nullable();
			$table->string('action', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_feed_requests_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_feedbacks', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->longText('suggestion')->nullable();
			$table->integer('new_machine')->nullable();
			$table->integer('building_expansion')->nullable();
			$table->integer('problem_at_client_site')->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_service_report_jobs', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->date('date')->nullable();
			$table->integer('labour')->nullable();
			$table->longText('job_perform')->nullable();
			$table->integer('food_rate')->nullable()->comment('food rate price per person');
			$table->integer('labour_leader')->nullable();
			$table->integer('labour_non_leader')->nullable();
			$table->string('working_type_value', 255)->nullable();
			$table->integer('overtime_hour')->nullable();
			$table->float('overtime_constant_1', 10, 5)->nullable();
			$table->float('overtime_constant_2', 10, 5)->nullable();
			$table->integer('accommodation')->nullable();
			$table->integer('accommodation_rate')->nullable();
			$table->float('travel_meter_rate', 10, 5)->nullable();
			$table->integer('travel_hour')->nullable();
			$table->float('travel_hour_constant', 5, 5)->nullable();
			$table->time('working_time_start')->nullable();
			$table->time('working_time_end')->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_jobs_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_logistics', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->integer('vehicle_id')->nullable();
			$table->longText('description')->nullable();
			$table->float('charge', 10, 5)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
		});
		Schema::create('ics_service_report_models', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->integer('model_id')->nullable();
			$table->string('test_run_machine', 255)->nullable();
			$table->string('serial_no', 255)->nullable();
			$table->string('test_capacity', 255)->nullable();
			$table->string('duration', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->index(['model_id'], 'model_id');
			$table->foreign('service_report_id', 'ics_service_report_models_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
			$table->foreign('model_id', 'ics_service_report_models_ibfk_2')->references('id')->on('ics_machine_models')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_overtime_codes', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->string('code', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->comment('for overtime code');
		});
		Schema::create('ics_service_report_parts', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->string('part_accessory', 255)->nullable();
			$table->string('qty', 11)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_parts_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_service_report_serials', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_id')->nullable();
			$table->string('serial', 255)->nullable();
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_id'], 'service_report_id');
			$table->foreign('service_report_id', 'ics_service_report_serials_ibfk_1')->references('id')->on('ics_service_reports')->restrictOnDelete()->restrictOnUpdate();
		});
		Schema::create('ics_sr_job_details', function (Blueprint $table) {
			$table->integer('id', true);
			$table->integer('service_report_job_id')->nullable();
			$table->string('destination_start', 255)->nullable();
			$table->string('destination_end', 255)->nullable();
			$table->integer('meter_start')->nullable();
			$table->integer('meter_end')->nullable();
			$table->time('time_start')->nullable();
			$table->time('time_end')->nullable();
			$table->tinyInteger('return')->nullable()->comment('0 => on the trip');
			$table->string('remarks', 255)->nullable();
			$table->dateTime('created_at')->nullable();
			$table->dateTime('updated_at')->nullable();
			$table->primary('id');
			$table->index(['service_report_job_id'], 'service_report_job_id');
			$table->foreign('service_report_job_id', 'ics_sr_job_details_ibfk_1')->references('id')->on('ics_service_report_jobs')->restrictOnDelete()->restrictOnUpdate();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// children first, then parent tables
		Schema::dropIfExists('ics_sr_job_details');
		Schema::dropIfExists('ics_service_report_serials');
		Schema::dropIfExists('ics_service_report_parts');
		Schema::dropIfExists('ics_service_report_overtime_codes');
		Schema::dropIfExists('ics_service_report_models');
		Schema::dropIfExists('ics_service_report_logistics');
		Schema::dropIfExists('ics_service_report_jobs');
		Schema::dropIfExists('ics_service_report_feedbacks');
		Schema::dropIfExists('ics_service_report_feed_requests');
		Schema::dropIfExists('ics_service_report_feed_problems');
		Schema::dropIfExists('ics_service_report_feed_items');
		Schema::dropIfExists('ics_service_report_feed_calls');
		Schema::dropIfExists('ics_service_report_discounts');
		Schema::dropIfExists('ics_service_report_complaints');
		Schema::dropIfExists('ics_service_report_attendees_phone');
		Schema::dropIfExists('ics_service_report_attendees');
		Schema::dropIfExists('ics_service_report_additional_charges');
		Schema::dropIfExists('ics_service_reports');
		Schema::dropIfExists('ics_working_types');
		Schema::dropIfExists('ics_status');
		Schema::dropIfExists('ics_proceeds');
		Schema::dropIfExists('ics_machine_models');
		Schema::dropIfExists('ics_charges');
		Schema::dropIfExists('ics_categories');
		Schema::dropIfExists('ics_accommodation_rates');
	}
};
