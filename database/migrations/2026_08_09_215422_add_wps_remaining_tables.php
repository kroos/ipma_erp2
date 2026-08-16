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
        // wps_delivery_method + wps_job_no_drawing + wps_job_no_revision +
        // wps_job_scope_job_no were present in the 180-table ipmaerp.sql dump but
        // were dropped from the 165-table migration set. wps_customer_order_manual
        // references wps_delivery_method (FK wps_customer_order_manual_ibfk_3), so
        // the target table and the missing FK are restored here.
        Schema::create('wps_delivery_method', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('delivery_method', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        // dump: id int NOT NULL (no auto-increment) — kept verbatim
        Schema::create('wps_job_no_drawing', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('job_no_id')->nullable();
            $table->integer('code')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->integer('author_id')->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->integer('created_by')->nullable();
            $table->longText('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['author_id'], 'author_id');
            $table->index(['created_by'], 'created_by');
            $table->foreign('author_id', 'wps_job_no_drawing_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('created_by', 'wps_job_no_drawing_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('wps_job_no_revision', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('job_scope_id')->nullable();
            $table->integer('amend_by')->nullable();
            $table->string('pdf', 255)->nullable();
            $table->integer('revision')->nullable()->default(1);
            $table->longText('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['job_scope_id'], 'job_scope_id');
            $table->index(['amend_by'], 'amend_by');
            $table->foreign('job_scope_id', 'wps_job_no_revision_ibfk_1')->references('id')->on('wps_com_job_scope')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('amend_by', 'wps_job_no_revision_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('wps_job_scope_job_no', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('job_scope_id')->nullable();
            $table->longText('description')->nullable();
            $table->integer('quantity')->nullable();
            $table->longText('remarks_A')->nullable();
            $table->longText('remarks_B')->nullable();
            $table->date('job_sheet_rec')->nullable();
            $table->tinyInteger('work_progress_turret_punch')->nullable();
            $table->tinyInteger('work_progress_plasma_cutting')->nullable();
            $table->tinyInteger('work_progress_cutting')->nullable();
            $table->tinyInteger('work_progress_bending')->nullable();
            $table->tinyInteger('work_progress_welding')->nullable();
            $table->tinyInteger('work_progress_machining')->nullable();
            $table->tinyInteger('work_progress_painting')->nullable();
            $table->tinyInteger('work_progress_automation')->nullable();
            $table->tinyInteger('work_progress_assembly')->nullable();
            $table->dateTime('completed')->nullable();
            $table->longText('remarks_completed')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('approved_by')->nullable();
            $table->dateTime('approved_date')->nullable();
            $table->integer('send_by')->nullable();
            $table->dateTime('send_date')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        // restore the FK the misc migration had to skip because wps_delivery_method
        // was missing from the migration set at the time.
        Schema::table('wps_customer_order_manual', function (Blueprint $table) {
            $table->foreign('delivery_method_id', 'wps_customer_order_manual_ibfk_3')->references('id')->on('wps_delivery_method')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wps_customer_order_manual', function (Blueprint $table) {
            $table->dropForeign('wps_customer_order_manual_ibfk_3');
        });

        Schema::dropIfExists('wps_job_scope_job_no');
        Schema::dropIfExists('wps_job_no_drawing');
        Schema::dropIfExists('wps_job_no_revision');
        Schema::dropIfExists('wps_delivery_method');
    }
};
