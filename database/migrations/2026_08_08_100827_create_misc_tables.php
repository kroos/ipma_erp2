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
        Schema::create('todo_categories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('category', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('todo_priorities', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('priority', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('todo_schedules', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('created_by')->nullable();
            $table->integer('category_id')->nullable();
            $table->longText('task')->nullable();
            $table->longText('description')->nullable();
            $table->integer('period_reminder')->nullable();
            $table->date('dateline')->nullable();
            $table->integer('priority_id')->nullable();
            $table->tinyInteger('active')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['category_id'], 'category_id');
            $table->index(['priority_id'], 'priority_id');
            $table->index(['created_by'], 'created_by');
            $table->foreign('category_id', 'todo_schedules_ibfk_1')->references('id')->on('todo_categories')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('priority_id', 'todo_schedules_ibfk_2')->references('id')->on('todo_priorities')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('created_by', 'todo_schedules_ibfk_3')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('todo_list', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('schedule_id')->nullable();
            $table->date('reminder')->nullable();
            $table->date('dateline')->nullable();
            $table->tinyInteger('completed')->nullable();
            $table->longText('description')->nullable();
            $table->integer('update_by')->nullable();
            $table->integer('priority_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['schedule_id'], 'schedule_id');
            $table->index(['priority_id'], 'priority_id');
            $table->index(['update_by'], 'update_by');
            $table->foreign('schedule_id', 'todo_list_ibfk_1')->references('id')->on('todo_schedules')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('priority_id', 'todo_list_ibfk_2')->references('id')->on('todo_priorities')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('update_by', 'todo_list_ibfk_3')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('todo_staffs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('schedule_id')->nullable();
            $table->integer('staff_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['schedule_id'], 'schedule_id');
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('schedule_id', 'todo_staffs_ibfk_1')->references('id')->on('todo_schedules')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('staff_id', 'todo_staffs_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        // wps_customer_order_manual — dump ibfk_3 targets wps_delivery_method. That table is not
        // created in this migration set; the FK is restored by
        // 2026_08_09_215422_add_wps_remaining_tables.php. Column + index are kept verbatim here.
        Schema::create('wps_customer_order_manual', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->date('order_date')->nullable();
            $table->integer('com_no')->nullable();
            $table->integer('com_year')->nullable();
            $table->longText('quotation_no')->nullable();
            $table->integer('customer_id')->nullable();
            $table->longText('description')->nullable();
            $table->date('target_delivery_date')->nullable();
            $table->integer('delivery_method_id')->nullable();
            $table->longText('special_delivery')->nullable();
            $table->tinyInteger('urgent')->nullable();
            $table->integer('send_id')->nullable();
            $table->dateTime('send_date')->nullable();
            $table->longText('send_remarks')->nullable();
            $table->integer('approval_id')->nullable();
            $table->dateTime('approval_date')->nullable();
            $table->longText('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['customer_id'], 'customer_id');
            $table->index(['delivery_method_id'], 'delivery_method_id');
            $table->index(['send_id'], 'send_id');
            $table->index(['approval_id'], 'approval_id');
            $table->foreign('staff_id', 'wps_customer_order_manual_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('send_id', 'wps_customer_order_manual_ibfk_4')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('approval_id', 'wps_customer_order_manual_ibfk_5')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('customer_id', 'wps_customer_order_manual_ibfk_6')->references('id')->on('customers')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('wps_com_job_scope', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('com_id')->nullable();
            $table->integer('sort')->nullable();
            $table->longText('description')->nullable()->comment('actually, this should be item id');
            $table->integer('quantity')->nullable();
            $table->string('unit', 255)->nullable()->comment('unit for the quantity');
            $table->tinyInteger('fabricate')->nullable();
            $table->tinyInteger('order')->nullable();
            $table->tinyInteger('store')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['com_id'], 'com_id');
            $table->foreign('com_id', 'wps_com_job_scope_ibfk_1')->references('id')->on('wps_customer_order_manual')->restrictOnDelete()->restrictOnUpdate();
        });

        // dump: id int NOT NULL DEFAULT 1 (no auto-increment) — kept verbatim
        Schema::create('wps_com_revision', function (Blueprint $table) {
            $table->integer('id')->default(1);
            $table->integer('com_id')->nullable();
            $table->integer('amend_by')->nullable();
            $table->string('pdf', 255)->nullable();
            $table->integer('revision')->nullable();
            $table->longText('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['com_id'], 'com_id');
            $table->index(['amend_by'], 'amend_by');
            $table->foreign('com_id', 'wps_com_revision_ibfk_1')->references('id')->on('wps_customer_order_manual')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('amend_by', 'wps_com_revision_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('ci_staffcicategoryitemcheck', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pivot_staff_item_id')->nullable();
            $table->integer('staff_id')->nullable();
            $table->integer('cicategory_item_id')->nullable();
            $table->integer('week_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['week_id'], 'week_id');
            $table->index(['pivot_staff_item_id'], 'pivot_staff_item_id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['cicategory_item_id'], 'cicategory_item_id');
            $table->foreign('week_id', 'ci_staffcicategoryitemcheck_ibfk_2')->references('id')->on('option_week_dates')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('pivot_staff_item_id', 'ci_staffcicategoryitemcheck_ibfk_3')->references('id')->on('pivot_staff_ci_category_item')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('staff_id', 'ci_staffcicategoryitemcheck_ibfk_4')->references('id')->on('staffs')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('cicategory_item_id', 'ci_staffcicategoryitemcheck_ibfk_5')->references('id')->on('ci_category_items')->nullOnDelete()->cascadeOnUpdate();
        });

        Schema::create('hr_appraisal_marks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('pivot_apoint_id')->nullable()->comment('pivot_apoint_appraisals');
            $table->integer('section_id')->nullable()->comment('hr_appraisal_sections');
            $table->integer('section_sub_id')->nullable()->comment('hr_appraisal_section_subs');
            $table->integer('main_question_id')->nullable()->comment('hr_appraisal_main_questions');
            $table->integer('question_id')->nullable()->comment('hr_appraisal_questions');
            $table->string('mark', 255)->nullable()->comment('marks given');
            $table->text('remark')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['pivot_apoint_id'], 'pivot_apoint_id');
            $table->index(['section_id'], 'section_id');
            $table->index(['section_sub_id'], 'section_sub_id');
            $table->index(['main_question_id'], 'main_question_id');
            $table->index(['question_id'], 'question_id');
            $table->foreign('pivot_apoint_id', 'hr_appraisal_marks_ibfk_1')->references('id')->on('pivot_apoint_appraisals')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('section_id', 'hr_appraisal_marks_ibfk_2')->references('id')->on('hr_appraisal_sections')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('section_sub_id', 'hr_appraisal_marks_ibfk_3')->references('id')->on('hr_appraisal_section_subs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('main_question_id', 'hr_appraisal_marks_ibfk_4')->references('id')->on('hr_appraisal_main_questions')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('question_id', 'hr_appraisal_marks_ibfk_5')->references('id')->on('hr_appraisal_questions')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_appraisal_marks');
        Schema::dropIfExists('ci_staffcicategoryitemcheck');
        Schema::dropIfExists('wps_com_revision');
        Schema::dropIfExists('wps_com_job_scope');
        Schema::dropIfExists('wps_customer_order_manual');
        Schema::dropIfExists('todo_staffs');
        Schema::dropIfExists('todo_list');
        Schema::dropIfExists('todo_schedules');
        Schema::dropIfExists('todo_priorities');
        Schema::dropIfExists('todo_categories');
    }
};
