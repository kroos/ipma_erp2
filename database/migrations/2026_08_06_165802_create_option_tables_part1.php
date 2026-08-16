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
        Schema::create('option_amounts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('amount', 255)->nullable();
            $table->string('operation', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['id', 'operation'], 'id');
        });
        Schema::create('option_appraisal_categories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('category', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_authorities', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('authorise', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_banks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('bank', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_branches', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['location'], 'location');
        });
        Schema::create('option_categories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 255)->nullable();
            $table->string('category', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_countries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('country', 255)->nullable();
            $table->string('currency', 255)->nullable();
            $table->string('currency_symbol', 255)->nullable();
            $table->string('iso_code', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['country'], 'country');
        });
        Schema::create('option_currencies', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('currency', 255)->nullable();
            $table->string('iso_code', 255)->nullable();
            $table->string('symbol', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_daytypes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('daytype', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_departments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('department', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_disciplinary_actions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('disciplinary_action', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_disciplines', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('discipline', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->decimal('merit_point', 4, 2)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['discipline'], 'discipline');
        });
        Schema::create('option_discount_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('discount_type', 255)->nullable();
            $table->string('operation', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_div', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('div', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_driving_licenses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('class', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['class'], 'class');
        });
        Schema::create('option_education_levels', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('education_level', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['education_level'], 'education_level');
        });
        Schema::create('option_genders', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 255)->nullable();
            $table->string('gender', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['gender'], 'gender');
        });
        Schema::create('option_groups', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('group', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_halfday_type', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('half_type', 255)->nullable();
            $table->string('desc', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_health_statuses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('health_status', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique(['health_status'], 'health_status');
        });
        Schema::create('option_infractions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('infraction', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_date')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('option_leave_statuses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('short_name', 255)->nullable();
            $table->string('status', 255)->nullable();
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
        Schema::dropIfExists('option_amounts');
        Schema::dropIfExists('option_appraisal_categories');
        Schema::dropIfExists('option_authorities');
        Schema::dropIfExists('option_banks');
        Schema::dropIfExists('option_branches');
        Schema::dropIfExists('option_categories');
        Schema::dropIfExists('option_countries');
        Schema::dropIfExists('option_currencies');
        Schema::dropIfExists('option_daytypes');
        Schema::dropIfExists('option_departments');
        Schema::dropIfExists('option_disciplinary_actions');
        Schema::dropIfExists('option_disciplines');
        Schema::dropIfExists('option_discount_types');
        Schema::dropIfExists('option_div');
        Schema::dropIfExists('option_driving_licenses');
        Schema::dropIfExists('option_education_levels');
        Schema::dropIfExists('option_genders');
        Schema::dropIfExists('option_groups');
        Schema::dropIfExists('option_halfday_type');
        Schema::dropIfExists('option_health_statuses');
        Schema::dropIfExists('option_infractions');
        Schema::dropIfExists('option_leave_statuses');
    }
};
