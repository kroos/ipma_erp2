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
        Schema::create('customers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('customer', 255)->nullable();
            $table->string('contact', 255)->nullable();
            $table->longText('address')->nullable();
            $table->string('add1', 255)->nullable();
            $table->string('add2', 255)->nullable();
            $table->string('add3', 255)->nullable();
            $table->string('add4', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('area', 50)->nullable();
            $table->string('latitude', 255)->nullable();
            $table->string('longitude', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            // dump: FULLTEXT INDEX cust_name(customer)
            $table->fullText('customer', 'cust_name');
        });

        Schema::create('staffs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('status_id')->default(2)->comment('Permanent/Probation/Others');
            $table->string('name', 255);
            $table->string('ic', 255)->nullable();
            $table->integer('authorise_id')->nullable()->comment('authorise to view system fully or partially');
            $table->integer('restday_group_id')->nullable()->comment('saturday grouping');
            $table->integer('religion_id')->nullable()->comment('staff religion');
            $table->integer('gender_id')->nullable()->comment('staff gender');
            $table->integer('race_id')->nullable()->comment('staff race');
            $table->integer('nationality_id')->nullable()->comment('citizenship');
            $table->integer('marital_status_id')->nullable();
            $table->integer('leave_flow_id')->nullable()->default(0)->comment('process flow of the leave');
            $table->integer('div_id')->nullable()->comment('accept leave');
            $table->integer('appraisal_category_id')->nullable()->comment('option_appraisal_categories');
            $table->string('email', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('place_of_birth', 255)->nullable();
            $table->string('mobile', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->date('dob')->nullable()->comment('date of birth');
            $table->integer('bank_type_id')->nullable()->comment('type of bank');
            $table->string('cimb_account', 255)->nullable()->comment('bank account number');
            $table->string('epf_account', 255)->nullable()->comment('kwsp account');
            $table->string('income_tax_no', 255)->nullable();
            $table->string('socso_no', 255)->nullable();
            $table->float('weight')->nullable();
            $table->float('height')->nullable();
            // dump: bit(1) DEFAULT b'1' — expressed as boolean (tinyint(1))
            $table->boolean('active')->nullable()->default(1)->comment('staff is active(1) or disable(0)');
            $table->date('join')->nullable()->comment('join company date');
            $table->date('confirmed')->nullable()->comment('confirm date');
            $table->string('remarks', 255)->nullable();
            $table->string('image', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['status_id'], 'status_id');
            $table->index(['religion_id'], 'religion_id');
            $table->index(['gender_id'], 'gender_id');
            $table->index(['race_id'], 'race_id');
            // dump: INDEX country_id(nationality_id)
            $table->index(['nationality_id'], 'country_id');
            $table->index(['marital_status_id'], 'marital_status_id');
            // dump: INDEX staffs_ibfk_10(restday_group_id)
            $table->index(['restday_group_id'], 'staffs_ibfk_10');
            $table->index(['leave_flow_id'], 'leave_flow_id');
            $table->index(['div_id'], 'div_id');
            $table->index(['authorise_id'], 'authorise_id');
            $table->index(['appraisal_category_id'], 'appraisal_category_id');
            // FK staffs_ibfk_11 + staffs_ibfk_13 (leave_flow_id -> hr_leave_approval_flow)
            // added at the end of migration 100820 (circular dependency).
            $table->foreign('restday_group_id', 'staffs_ibfk_2')->references('id')->on('option_restday_groups')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('religion_id', 'staffs_ibfk_3')->references('id')->on('option_religions')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('gender_id', 'staffs_ibfk_4')->references('id')->on('option_genders')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('race_id', 'staffs_ibfk_5')->references('id')->on('option_races')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('nationality_id', 'staffs_ibfk_6')->references('id')->on('option_countries')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('marital_status_id', 'staffs_ibfk_7')->references('id')->on('option_marital_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('status_id', 'staffs_ibfk_12')->references('id')->on('option_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('div_id', 'staffs_ibfk_14')->references('id')->on('option_div')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('authorise_id', 'staffs_ibfk_15')->references('id')->on('option_authorities')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('appraisal_category_id', 'staffs_ibfk_16')->references('id')->on('option_appraisal_categories')->restrictOnDelete()->restrictOnUpdate();
        });

        // cs_orders (created in 165800) has FKs to staffs/customers — forward dependency, appended here.
        Schema::table('cs_orders', function (Blueprint $table) {
            $table->foreign('informed_by', 'cs_orders_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('pic', 'cs_orders_ibfk_3')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('customer_id', 'cs_orders_ibfk_4')->references('id')->on('customers')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop the cs_orders FK constraints appended in up() before dropping staffs/customers
        Schema::table('cs_orders', function (Blueprint $table) {
            $table->dropForeign('cs_orders_ibfk_2');
            $table->dropForeign('cs_orders_ibfk_3');
            $table->dropForeign('cs_orders_ibfk_4');
        });
        // children first — hr_leave_tables' staffs FK lives on staffs itself (no child FK from
        // this file's tables to staffs), drop staffs then customers
        Schema::dropIfExists('staffs');
        Schema::dropIfExists('customers');
    }
};
