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
        Schema::create('hr_memo_categories', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('memo_category')->nullable();
            $table->float('merit_point')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('hr_childrens', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->string('children', 255)->nullable();
            $table->date('dob')->nullable();
            $table->integer('gender_id')->nullable();
            $table->integer('education_level_id')->nullable();
            $table->integer('health_status_id')->nullable();
            // dump: tinyint(1) UNSIGNED ZEROFILL — no Laravel equivalent, use unsigned tinyint
            $table->tinyInteger('tax_exemption')->unsigned()->nullable()->default(0);
            $table->integer('tax_exemption_percentage_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique('children');
            $table->index(['education_level_id'], 'education_level_id');
            $table->index(['health_status_id'], 'health_status_id');
            $table->index(['tax_exemption_percentage_id'], 'tax_exemption_percentage_id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['gender_id'], 'gender_id');
            $table->foreign('health_status_id', 'hr_childrens_ibfk_2')->references('id')->on('option_health_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('tax_exemption_percentage_id', 'hr_childrens_ibfk_3')->references('id')->on('option_tax_exemption_percentages')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('staff_id', 'hr_childrens_ibfk_4')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('gender_id', 'hr_childrens_ibfk_5')->references('id')->on('option_genders')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('education_level_id', 'hr_childrens_ibfk_6')->references('id')->on('option_education_levels')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_disciplinary', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable()->comment('staff');
            $table->integer('supervisor_id')->nullable()->comment('staff');
            $table->integer('disciplinary_action_id')->nullable()->comment('hr_disciplinary_types');
            $table->integer('violation_id')->nullable()->comment('option_violations');
            $table->integer('infraction_id')->nullable()->comment('option_infractions');
            $table->date('misconduct_date')->nullable()->comment('incident happen date');
            $table->date('action_taken_date')->nullable()->comment('action taken date');
            $table->longText('reason')->nullable()->comment('description for the incident');
            $table->longText('action_to_be_taken')->nullable()->comment('action taken');
            $table->string('softcopy', 255)->nullable()->comment('softcopy of warning letter');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['disciplinary_action_id'], 'disciplinary_action_id');
            $table->index(['staff_id'], 'staff_id');
            // dump quirk: index name desc_id on the violation_id column — kept verbatim
            $table->index(['violation_id'], 'desc_id');
            $table->index(['supervisor_id'], 'supervisor_id');
            $table->index(['infraction_id'], 'infraction_id');
            $table->foreign('disciplinary_action_id', 'hr_disciplinary_ibfk_1')->references('id')->on('option_disciplinary_actions')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('staff_id', 'hr_disciplinary_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('violation_id', 'hr_disciplinary_ibfk_3')->references('id')->on('option_violations')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('supervisor_id', 'hr_disciplinary_ibfk_4')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('infraction_id', 'hr_disciplinary_ibfk_5')->references('id')->on('option_infractions')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_driving_licenses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->integer('driving_license_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['driving_license_id'], 'driving_license_id');
            $table->foreign('staff_id', 'hr_driving_licenses_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('driving_license_id', 'hr_driving_licenses_ibfk_2')->references('id')->on('option_driving_licenses')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_educations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id');
            $table->text('institution')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->text('qualification')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('staff_id', 'hr_educations_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_emergency', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->string('contact_person', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->integer('relationship_id')->nullable();
            $table->text('address')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['contact_person'], 'contact_person');
            // dump quirk: index name relationship on the relationship_id column — kept verbatim
            $table->index(['relationship_id'], 'relationship');
            $table->foreign('staff_id', 'hr_emergency_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('relationship_id', 'hr_emergency_ibfk_2')->references('id')->on('option_relationships')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_lockers', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('locker', 255)->nullable();
            $table->integer('locker_status_id');
            $table->integer('category_id');
            $table->integer('location_id')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->unique('locker');
            $table->index(['locker_status_id'], 'locker_status_id');
            $table->index(['location_id'], 'locker_location_id');
            $table->index(['category_id'], 'locker_category_id');
            $table->foreign('locker_status_id', 'hr_lockers_ibfk_1')->references('id')->on('option_locker_statuses')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('location_id', 'hr_lockers_ibfk_4')->references('id')->on('option_branches')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('category_id', 'hr_lockers_ibfk_5')->references('id')->on('option_categories')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_memos', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->integer('memo_category')->nullable();
            $table->date('date')->nullable();
            $table->longText('reason')->nullable();
            $table->integer('merit_point')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['memo_category'], 'memo_category');
            $table->foreign('staff_id', 'hr_memos_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('memo_category', 'hr_memos_ibfk_2')->references('id')->on('hr_memo_categories')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_resign', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->date('date')->nullable();
            $table->string('desc', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });

        Schema::create('hr_spouses', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->string('spouse', 255)->nullable();
            $table->string('id_card_passport', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->date('dob')->nullable();
            $table->string('profession', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('staff_id', 'hr_spouses_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_restday_calendars', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('restday_group_id')->nullable();
            $table->date('saturday_date')->nullable();
            $table->date('friday_date')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['restday_group_id'], 'restday_group_id');
            $table->foreign('restday_group_id', 'hr_restday_calendars_ibfk_1')->references('id')->on('option_restday_groups')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_overtime_ranges', function (Blueprint $table) {
            $table->integer('id', true);
            $table->time('start')->nullable()->comment('start time');
            $table->time('end')->nullable()->comment('end time');
            $table->time('total_time')->nullable();
            $table->string('total_hour', 255)->nullable()->comment('total overtime hour');
            $table->tinyInteger('ramadhan')->nullable()->comment('1=ramadhan use, 0=normal day use');
            $table->tinyInteger('active')->nullable()->comment('1=active, 0=disable');
            $table->dateTime('created_date')->nullable();
            $table->dateTime('updated_date')->nullable();
            $table->dateTime('deleted_date')->nullable();
            $table->primary('id');
        });

        Schema::create('hr_overtimes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('ot_date')->nullable()->comment('date of ot');
            $table->integer('staff_id')->nullable()->comment('staff who ot');
            $table->integer('assign_staff_id')->nullable()->comment('supervisor who assign overtime for the staff');
            $table->integer('overtime_range_id')->nullable()->comment('overtime time range');
            $table->tinyInteger('active')->nullable()->comment('1=active, 0=disable');
            $table->string('remark', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->string('temp_username', 255)->nullable()->comment('temporary created by who');
            $table->string('temp_hour', 255)->nullable()->comment('temporary hour');
            $table->string('temp_end', 255)->nullable()->comment('temporary end');
            $table->primary('id');
            // dump quirk: index name staff_id on the assign_staff_id column — kept verbatim
            $table->index(['assign_staff_id'], 'staff_id');
            $table->index(['staff_id'], 'staff_id_2');
            $table->index(['overtime_range_id'], 'overtime_range_id');
            $table->foreign('assign_staff_id', 'hr_overtimes_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('staff_id', 'hr_overtimes_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('overtime_range_id', 'hr_overtimes_ibfk_3')->references('id')->on('hr_overtime_ranges')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_outstations', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->integer('customer_id')->nullable();
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            // dump: bit(1) — stored as tinyint, functionally boolean
            $table->tinyInteger('active')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->index(['customer_id'], 'customer_id');
            $table->foreign('staff_id', 'hr_outstations_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('customer_id', 'hr_outstations_ibfk_2')->references('id')->on('customers')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_outstation_attendances', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->integer('outstation_id')->nullable();
            $table->date('date_attend')->nullable();
            $table->time('in')->nullable();
            $table->string('in_latitude', 255)->nullable();
            $table->string('in_longitude', 255)->nullable();
            $table->string('in_regionName', 255)->nullable();
            $table->string('in_cityName', 255)->nullable();
            $table->time('out')->nullable();
            $table->string('out_latitude', 255)->nullable();
            $table->string('out_longitude', 255)->nullable();
            $table->string('out_regionName', 255)->nullable();
            $table->string('out_cityName', 255)->nullable();
            $table->integer('confirm')->nullable();
            $table->dateTime('date_confirm')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['outstation_id'], 'outstation_id');
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('outstation_id', 'hr_outstation_attendances_ibfk_1')->references('id')->on('hr_outstations')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('staff_id', 'hr_outstation_attendances_ibfk_2')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('logins', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id')->nullable();
            $table->string('username', 7);
            $table->string('password', 255);
            $table->string('remember_token', 255)->nullable();
            $table->tinyInteger('active')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->string('temp_name', 255)->nullable();
            $table->primary('id');
            $table->unique('username');
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('staff_id', 'logins_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_attendance_payslip_settings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('description', 255)->nullable();
            $table->float('value')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('hr_attendance_remarks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->string('attendance_remarks', 255)->nullable();
            $table->string('hr_attendance_remarks', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            $table->foreign('staff_id', 'hr_attendance_remarks_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
        });

        Schema::create('hr_holiday_calendars', function (Blueprint $table) {
            $table->integer('id', true);
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->longText('holiday')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->primary('id');
        });

        Schema::create('hr_temp_punch_time', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('EmployeeCode', 255)->nullable()->comment('staff username');
            $table->string('Att_Time', 255)->nullable()->comment('punch time');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });

        // hr_attendances last — it has FKs to hr_leaves (100820), hr_overtimes, hr_outstations (this file)
        Schema::create('hr_attendances', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('staff_id');
            $table->integer('daytype_id');
            $table->integer('attendance_type_id')->nullable();
            $table->date('attend_date');
            $table->time('in')->nullable();
            $table->time('break')->nullable();
            $table->time('resume')->nullable();
            $table->time('out')->nullable();
            $table->time('time_work_hour')->nullable();
            $table->float('work_hour')->nullable()->comment('total working hour');
            $table->integer('overtime_id')->nullable()->comment('auto populate when load attendance page');
            $table->integer('leave_id')->nullable()->comment('auto populate when load attendance page');
            $table->integer('outstation_id')->nullable()->comment('auto populate when load attendance page');
            $table->longText('remarks')->nullable()->comment('remark for everyone view');
            $table->longText('hr_remarks')->nullable()->comment('remark only for hr view');
            $table->integer('exception')->default(0)->comment('1=exception, 0=no exception');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
            $table->index(['staff_id'], 'staff_id');
            // dump quirk: index name daytype on the daytype_id column — kept verbatim
            $table->index(['daytype_id'], 'daytype');
            // dump quirk: index name leave on the attendance_type_id column — kept verbatim
            $table->index(['attendance_type_id'], 'leave');
            $table->index(['leave_id'], 'leave_id');
            $table->index(['overtime_id'], 'overtime_id');
            $table->index(['outstation_id'], 'outstation_id');
            $table->foreign('staff_id', 'hr_attendances_ibfk_1')->references('id')->on('staffs')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('daytype_id', 'hr_attendances_ibfk_2')->references('id')->on('option_daytypes')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('attendance_type_id', 'hr_attendances_ibfk_3')->references('id')->on('option_tcms')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('leave_id', 'hr_attendances_ibfk_4')->references('id')->on('hr_leaves')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('overtime_id', 'hr_attendances_ibfk_5')->references('id')->on('hr_overtimes')->restrictOnDelete()->restrictOnUpdate();
            $table->foreign('outstation_id', 'hr_attendances_ibfk_6')->references('id')->on('hr_outstations')->restrictOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // children first — hr_attendances references hr_overtimes/hr_outstations from this file
        Schema::dropIfExists('hr_attendances');
        Schema::dropIfExists('hr_temp_punch_time');
        Schema::dropIfExists('hr_holiday_calendars');
        Schema::dropIfExists('hr_attendance_remarks');
        Schema::dropIfExists('hr_attendance_payslip_settings');
        Schema::dropIfExists('logins');
        Schema::dropIfExists('hr_outstation_attendances');
        Schema::dropIfExists('hr_outstations');
        Schema::dropIfExists('hr_overtimes');
        Schema::dropIfExists('hr_overtime_ranges');
        Schema::dropIfExists('hr_restday_calendars');
        Schema::dropIfExists('hr_spouses');
        Schema::dropIfExists('hr_resign');
        Schema::dropIfExists('hr_memos');
        Schema::dropIfExists('hr_lockers');
        Schema::dropIfExists('hr_emergency');
        Schema::dropIfExists('hr_educations');
        Schema::dropIfExists('hr_driving_licenses');
        Schema::dropIfExists('hr_disciplinary');
        Schema::dropIfExists('hr_childrens');
        Schema::dropIfExists('hr_memo_categories');
    }
};
