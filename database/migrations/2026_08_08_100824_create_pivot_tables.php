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
        Schema::create('pivot_dept_cate_branches', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('department_id')->nullable()->comment('which department');
            $table->integer('category_id')->nullable()->comment('office / production');
            $table->integer('branch_id')->nullable()->comment('factory or hq from other place');
            $table->integer('wh_group_id')->nullable()->comment('to link with option_working_hours with column group');
            $table->string('code', 255)->nullable();
            $table->string('department', 255)->nullable();
            $table->integer('staff_quota')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['department_id'], 'department_id');
            $table->index(['category_id'], 'category_id');
            $table->index(['branch_id'], 'branch_id');
            $table->foreign('department_id', 'pivot_dept_cate_branches_ibfk_1')->references('id')->on('option_departments')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('category_id', 'pivot_dept_cate_branches_ibfk_2')->references('id')->on('option_categories')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('branch_id', 'pivot_dept_cate_branches_ibfk_3')->references('id')->on('option_branches')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_apoint_appraisals', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('evaluator_id')->nullable()->comment('staffs | staff giving marks');
            $table->integer('evaluatee_id')->nullable()->comment('staffs | staff receive marks');
            // dump: year type — no Laravel equivalent, stored as smallint unsigned
            $table->unsignedSmallInteger('year')->nullable()->comment('year appraisal form populate');
            $table->integer('appraisal_category_id')->nullable()->comment('option_appraisal_categories');
            $table->integer('appraisal_category_version')->nullable()->comment('option_appraisal_categories');
            $table->string('full_mark', 255)->nullable()->comment('appraisal form full mark');
            $table->string('total_mark', 255)->nullable()->comment('total mark get from appraisal');
            $table->dateTime('finalise_date')->nullable()->comment('final submit date');
            $table->string('remark', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['evaluator_id'], 'evaluator_id');
            $table->index(['evaluatee_id'], 'evaluatee_id');
            // dump quirk: index name appraisal_categories_id on the appraisal_category_id column — kept verbatim
            $table->index(['appraisal_category_id'], 'appraisal_categories_id');
            $table->foreign('evaluator_id', 'pivot_apoint_appraisals_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('evaluatee_id', 'pivot_apoint_appraisals_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('appraisal_category_id', 'pivot_apoint_appraisals_ibfk_3')->references('id')->on('option_appraisal_categories')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_category_appraisals', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('category_id')->nullable();
            $table->integer('section_id')->nullable();
            $table->string('version', 255)->nullable();
            $table->integer('sort')->nullable();
            $table->string('remark', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['category_id'], 'category_id');
            $table->index(['section_id'], 'section_id');
            $table->foreign('category_id', 'pivot_category_appraisals_ibfk_1')->references('id')->on('option_appraisal_categories')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('section_id', 'pivot_category_appraisals_ibfk_2')->references('id')->on('hr_appraisal_sections')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_cross_backups', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable()->comment('target staff who need a cross backup');
            $table->integer('backup_staff_id')->nullable()->comment('staff who backup');
            $table->integer('active')->nullable()->comment('1=active / 0=not active');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['backup_staff_id'], 'backup_staff_id');
            $table->foreign('staff_id', 'pivot_cross_backups_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('backup_staff_id', 'pivot_cross_backups_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_leave_annuals', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leave_id')->nullable();
            $table->integer('leave_annual_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['leave_id'], 'leave_id');
            $table->index(['leave_annual_id'], 'leave_annual_id');
            $table->foreign('leave_id', 'pivot_leave_annuals_ibfk_1')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_annual_id', 'pivot_leave_annuals_ibfk_2')->references('id')->on('hr_leave_annuals')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_leave_maternities', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leave_id')->nullable();
            $table->integer('leave_maternity_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['leave_id'], 'leave_id');
            $table->index(['leave_maternity_id'], 'leave_maternity_id');
            $table->foreign('leave_id', 'pivot_leave_maternities_ibfk_1')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_maternity_id', 'pivot_leave_maternities_ibfk_2')->references('id')->on('hr_leave_maternities')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_leave_mc', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leave_id')->nullable();
            $table->integer('leave_mc_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['leave_id'], 'leave_id');
            // dump quirk: index name leave_annual_id on the leave_mc_id column — kept verbatim
            $table->index(['leave_mc_id'], 'leave_annual_id');
            $table->foreign('leave_id', 'pivot_leave_mc_ibfk_1')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_mc_id', 'pivot_leave_mc_ibfk_2')->references('id')->on('hr_leave_mc')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_leave_replacements', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('leave_id')->nullable();
            $table->integer('leave_replacement_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['leave_id'], 'leave_id');
            $table->index(['leave_replacement_id'], 'leave_replacement_id');
            $table->foreign('leave_id', 'pivot_leave_replacements_ibfk_1')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_replacement_id', 'pivot_leave_replacements_ibfk_2')->references('id')->on('hr_leave_replacements')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('pivot_sales_job_description_get_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sales_job_description_id')->nullable();
            $table->integer('sales_get_item_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['sales_job_description_id'], 'sales_job_description_id');
            $table->index(['sales_get_item_id'], 'sales_get_item_id');
            $table->foreign('sales_job_description_id', 'pivot_sales_job_description_get_items_ibfk_1')->references('id')->on('sales_job_descriptions')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('sales_get_item_id', 'pivot_sales_job_description_get_items_ibfk_2')->references('id')->on('option_sales_get_items')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('pivot_sales_sales_delivery', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sales_id')->nullable();
            $table->integer('sales_delivery_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['sales_id'], 'sales_id');
            $table->index(['sales_delivery_id'], 'sales_delivery_id');
        });

        Schema::create('pivot_staff_ci_category_item', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->integer('cicategory_item_id')->nullable();
            $table->dateTime('date_check')->nullable();
            $table->integer('week')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            // dump quirk: index name ci_category_item_id on the cicategory_item_id column — kept verbatim
            $table->index(['cicategory_item_id'], 'ci_category_item_id');
            $table->foreign('staff_id', 'pivot_staff_ci_category_item_ibfk_1')->references('id')->on('staffs')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('cicategory_item_id', 'pivot_staff_ci_category_item_ibfk_2')->references('id')->on('ci_category_items')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('pivot_staff_pivotdepts', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable()->comment('staff id');
            $table->integer('pivot_dept_id')->nullable()->comment('link to pivot dept cate branches table');
            $table->integer('main')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['pivot_dept_id'], 'pivot_dept_id');
            $table->foreign('staff_id', 'pivot_staff_pivotdepts_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('pivot_dept_id', 'pivot_staff_pivotdepts_ibfk_2')->references('id')->on('pivot_dept_cate_branches')->restrictOnDelete()->restrictOnUpdate();
        });

        // FK option_machine_ibfk_1 (department_id -> pivot_dept_cate_branches):
        // forward dependency — option_machine is created in 165803, pivot_dept_cate_branches here.
        Schema::table('option_machine', function (Blueprint $table) {
            $table->foreign('department_id', 'option_machine_ibfk_1')->references('id')->on('pivot_dept_cate_branches')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('option_machine', function (Blueprint $table) {
            $table->dropForeign('option_machine_ibfk_1');
        });
        Schema::dropIfExists('pivot_staff_pivotdepts');
        Schema::dropIfExists('pivot_staff_ci_category_item');
        Schema::dropIfExists('pivot_sales_sales_delivery');
        Schema::dropIfExists('pivot_sales_job_description_get_items');
        Schema::dropIfExists('pivot_leave_replacements');
        Schema::dropIfExists('pivot_leave_mc');
        Schema::dropIfExists('pivot_leave_maternities');
        Schema::dropIfExists('pivot_leave_annuals');
        Schema::dropIfExists('pivot_cross_backups');
        Schema::dropIfExists('pivot_category_appraisals');
        Schema::dropIfExists('pivot_apoint_appraisals');
        Schema::dropIfExists('pivot_dept_cate_branches');
    }
};
