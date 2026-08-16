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
        Schema::create('hr_appraisal_main_questions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('section_sub_id')->nullable()->comment('hr_appraisal_section_subs');
            $table->integer('mark')->nullable()->comment('mark for the question');
            $table->text('main_question')->nullable();
            $table->integer('sort')->nullable()->comment('sorting');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('hr_appraisal_questions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('main_question_id')->nullable()->comment('hr_appraisal_main_questions');
            $table->integer('mark')->nullable()->comment('mark for the question');
            $table->text('question')->nullable();
            $table->integer('sort')->nullable()->comment('sorting');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('hr_appraisal_section_subs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('section_id')->nullable()->comment('hr_appraisal_sections');
            $table->text('section_sub')->nullable();
            $table->integer('sort')->nullable()->comment('sorting');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('hr_appraisal_sections', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('section')->nullable();
            $table->string('sort', 255)->nullable()->comment('sorting');
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->primary('id');
        });
        Schema::create('hr_appraisal_settings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->float('value1')->nullable();
            $table->float('value2')->nullable();
            $table->float('value3')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable()->useCurrentOnUpdate();
            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_appraisal_main_questions');
        Schema::dropIfExists('hr_appraisal_questions');
        Schema::dropIfExists('hr_appraisal_section_subs');
        Schema::dropIfExists('hr_appraisal_sections');
        Schema::dropIfExists('hr_appraisal_settings');
    }
};
