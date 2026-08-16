<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hr_leave_approval_flow', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('backup_approval')->nullable();
            $table->integer('supervisor_approval')->nullable();
            $table->integer('hod_approval')->nullable();
            $table->integer('director_approval')->nullable();
            $table->integer('hr_approval')->nullable();
            $table->string('description', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('hr_leaves', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_no')->nullable()->comment('number of the leave renew every year');
            $table->integer('leave_year')->nullable()->comment('year of the leave apply');
            $table->integer('staff_id')->nullable();
            $table->integer('leave_type_id')->nullable()->comment('al/mc/upl');
            $table->integer('leave_cat')->nullable()->comment('determine for half day leave or more');
            $table->integer('half_type_id')->nullable()->comment('morning / afternoon');
            $table->dateTime('date_time_start')->nullable();
            $table->dateTime('date_time_end')->nullable();
            $table->text('reason')->nullable();
            $table->float('period_day', 5)->nullable()->comment('total day');
            $table->time('period_time')->nullable()->comment('total time');
            $table->string('softcopy', 255)->nullable()->comment('upload document softcopy');
            $table->boolean('hardcopy')->nullable()->comment('receive hardcopy = 1 / not receive hardcopy = null');
            $table->integer('leave_status_id')->nullable()->comment('user self active/reject/cancel');
            $table->string('verify_code', 255)->nullable()->comment('verification code to accept the leave');
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['staff_id'], 'staff_id');
            $table->index(['leave_type_id'], 'leave_id');
            $table->index(['leave_status_id'], 'active');
            $table->index(['half_type_id'], 'hr_leaves_ibfk_4');
            $table->foreign('staff_id', 'hr_leaves_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_type_id', 'hr_leaves_ibfk_2')->references('id')->on('option_leave_types')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_status_id', 'hr_leaves_ibfk_3')->references('id')->on('option_leave_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('half_type_id', 'hr_leaves_ibfk_4')->references('id')->on('option_halfday_type')->restrictOnDelete()->restrictOnUpdate();
        });

        // dump declares period_day as float(5, 1); Laravel's float() only emits
        // IEEE precision syntax (float(5)), so the M,D form is restored verbatim.
        DB::statement('ALTER TABLE `hr_leaves` MODIFY `period_day` float(5, 1) NULL');

        Schema::create('hr_leave_amends', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_id')->nullable()->comment('leave id');
            $table->integer('staff_id')->nullable()->comment('staff id who create amend');
            $table->dateTime('date')->nullable();
            $table->longText('amend_note')->nullable();
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['leave_id'], 'leave_id');
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('leave_id', 'hr_leave_amends_ibfk_1')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('staff_id', 'hr_leave_amends_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_leave_annuals', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('staff_id')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->float('annual_leave', 53)->nullable();
            $table->float('annual_leave_adjustment', 53)->nullable();
            $table->float('annual_leave_utilize', 53)->nullable();
            $table->float('annual_leave_balance', 53)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['staff_id'], 'staff_id');
            $table->index(['year'], 'year');
            $table->foreign('staff_id', 'hr_leave_annuals_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        // dump declares the leave entitlement columns as float(255, 1); MySQL treats
        // M >= 25 as double, matching float(53), but the M,D form is kept verbatim.
        DB::statement('ALTER TABLE `hr_leave_annuals` MODIFY `annual_leave` float(255, 1) NULL, MODIFY `annual_leave_adjustment` float(255, 1) NULL, MODIFY `annual_leave_utilize` float(255, 1) NULL, MODIFY `annual_leave_balance` float(255, 1) NULL');

        Schema::create('hr_leave_maternities', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('staff_id')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->float('maternity_leave', 53)->nullable();
            $table->float('maternity_leave_adjustment', 53)->nullable();
            $table->float('maternity_leave_utilize', 53)->nullable();
            $table->float('maternity_leave_balance', 53)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('staff_id', 'hr_leave_maternities_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        // dump declares these as float(255, 1) — kept verbatim (see hr_leave_annuals).
        DB::statement('ALTER TABLE `hr_leave_maternities` MODIFY `maternity_leave` float(255, 1) NULL, MODIFY `maternity_leave_adjustment` float(255, 1) NULL, MODIFY `maternity_leave_utilize` float(255, 1) NULL, MODIFY `maternity_leave_balance` float(255, 1) NULL');

        Schema::create('hr_leave_mc', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('staff_id')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->float('mc_leave', 53)->nullable();
            $table->float('mc_leave_adjustment', 53)->nullable();
            $table->float('mc_leave_utilize', 53)->nullable();
            $table->float('mc_leave_balance', 53)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('staff_id', 'hr_leave_mc_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        // dump declares these as float(255, 1) — kept verbatim (see hr_leave_annuals).
        DB::statement('ALTER TABLE `hr_leave_mc` MODIFY `mc_leave` float(255, 1) NULL, MODIFY `mc_leave_adjustment` float(255, 1) NULL, MODIFY `mc_leave_utilize` float(255, 1) NULL, MODIFY `mc_leave_balance` float(255, 1) NULL');

        Schema::create('hr_leave_replacements', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_id')->nullable();
            $table->integer('staff_id');
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->integer('customer_id')->nullable()->comment('customer id');
            $table->text('reason')->nullable();
            $table->float('leave_total', 4)->nullable()->comment('leave intialize / total leave day');
            $table->float('leave_utilize', 4)->nullable()->comment('leave used up');
            $table->float('leave_balance', 4)->nullable()->comment('balance leave left');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['staff_id'], 'staff_id');
            $table->index(['customer_id'], 'customer_id');
            $table->index(['leave_id'], 'leave_id');
            $table->foreign('leave_id', 'hr_leave_replacements_ibfk_1')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('staff_id', 'hr_leave_replacements_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('customer_id', 'hr_leave_replacements_ibfk_3')->references('id')->on('customers')->restrictOnDelete()->restrictOnUpdate();
        });

        // dump declares these as float(4, 1) — kept verbatim (see hr_leaves).
        DB::statement('ALTER TABLE `hr_leave_replacements` MODIFY `leave_total` float(4, 1) NULL, MODIFY `leave_utilize` float(4, 1) NULL, MODIFY `leave_balance` float(4, 1) NULL');

        Schema::create('hr_leave_approval_backups', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_id')->nullable()->comment('leave id');
            $table->integer('staff_id')->nullable()->comment('backup staff id');
            $table->integer('leave_status_id')->nullable()->comment('approve / reject / cancel / waive');
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['staff_id'], 'staff_id');
            $table->index(['leave_id'], 'staff_leave_id');
            $table->index(['leave_status_id'], 'leave_status_id');
            $table->foreign('staff_id', 'hr_leave_approval_backups_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_id', 'hr_leave_approval_backups_ibfk_2')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_status_id', 'hr_leave_approval_backups_ibfk_3')->references('id')->on('option_leave_statuses')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_leave_approval_directors', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_id')->nullable()->comment('leave id');
            $table->integer('staff_id')->nullable()->comment('approval staff id');
            $table->integer('leave_status_id')->nullable()->comment('approve / reject / cancel / waive');
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['leave_id'], 'staff_leave_id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['leave_status_id'], 'leave_status_id');
            $table->foreign('staff_id', 'hr_leave_approval_directors_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_id', 'hr_leave_approval_directors_ibfk_2')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_status_id', 'hr_leave_approval_directors_ibfk_3')->references('id')->on('option_leave_statuses')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_leave_approval_hods', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_id')->nullable()->comment('leave id');
            $table->integer('staff_id')->nullable()->comment('approval staff id');
            $table->integer('leave_status_id')->nullable()->comment('approve / reject / cancel / waive');
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['leave_id'], 'staff_leave_id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['leave_status_id'], 'leave_status_id');
            $table->foreign('staff_id', 'hr_leave_approval_hods_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_id', 'hr_leave_approval_hods_ibfk_2')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_status_id', 'hr_leave_approval_hods_ibfk_3')->references('id')->on('option_leave_statuses')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_leave_approval_hr', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_id')->nullable()->comment('leave id');
            $table->integer('staff_id')->nullable()->comment('approval staff id');
            $table->integer('leave_status_id')->nullable()->comment('approve / reject / cancel / waive');
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['leave_id'], 'staff_leave_id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['leave_status_id'], 'leave_status_id');
            $table->foreign('staff_id', 'hr_leave_approval_hr_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_id', 'hr_leave_approval_hr_ibfk_2')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_status_id', 'hr_leave_approval_hr_ibfk_3')->references('id')->on('option_leave_statuses')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_leave_approval_supervisors', function (Blueprint $table) {
            $table->integer('id', true);
            $table->primary('id');
            $table->integer('leave_id')->nullable()->comment('leave id');
            $table->integer('staff_id')->nullable()->comment('approval staff id');
            $table->integer('leave_status_id')->nullable()->comment('approve / reject / cancel / waive');
            $table->text('remarks')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->index(['staff_id'], 'staff_id');
            $table->index(['leave_id'], 'staff_leave_id');
            $table->index(['leave_status_id'], 'leave_status_id');
            $table->foreign('staff_id', 'hr_leave_approval_supervisors_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_id', 'hr_leave_approval_supervisors_ibfk_2')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_status_id', 'hr_leave_approval_supervisors_ibfk_3')->references('id')->on('option_leave_statuses')->restrictOnDelete()->restrictOnUpdate();
        });

        // Circular FK resolution (approach B): the dump declares duplicate constraints
        // staffs_ibfk_11 + staffs_ibfk_13 (staffs.leave_flow_id -> hr_leave_approval_flow.id,
        // both RESTRICT). 100816 created the leave_flow_id index only; the two FKs are
        // added here, after hr_leave_approval_flow exists, and dropped first in down().
        Schema::table('staffs', function (Blueprint $table) {
            $table->foreign('leave_flow_id', 'staffs_ibfk_11')->references('id')->on('hr_leave_approval_flow')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_flow_id', 'staffs_ibfk_13')->references('id')->on('hr_leave_approval_flow')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropForeign(['staffs_ibfk_11', 'staffs_ibfk_13']);
        });

        Schema::dropIfExists('hr_leave_approval_supervisors');
        Schema::dropIfExists('hr_leave_approval_hr');
        Schema::dropIfExists('hr_leave_approval_hods');
        Schema::dropIfExists('hr_leave_approval_directors');
        Schema::dropIfExists('hr_leave_approval_backups');
        Schema::dropIfExists('hr_leave_replacements');
        Schema::dropIfExists('hr_leave_mc');
        Schema::dropIfExists('hr_leave_maternities');
        Schema::dropIfExists('hr_leave_annuals');
        Schema::dropIfExists('hr_leave_amends');
        Schema::dropIfExists('hr_leaves');
        Schema::dropIfExists('hr_leave_approval_flow');
    }
};
