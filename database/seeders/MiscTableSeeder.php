<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MiscTableSeeder extends Seeder
{
    /**
     * Seed the application's database with exact data from ipmaerp.sql.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Defensive: only truncate tables that exist in this environment. Some of
        // these (todo_statuses, todos, wps_*, hse_*) are only present in the full
        // 180-table ipmaerp.sql dump, NOT in the 150-table migration set — a plain
        // truncate() would throw "table does not exist" and abort db:seed.
        $tables = [
            'todo_categories', 'todo_priorities', 'todo_statuses', 'todo_subtasks',
            'todo_staffs', 'todos', 'wps_categories', 'wps_items', 'wps_plants',
            'wps_quotations', 'wps_vehicles', 'wps_week_schedules', 'wps_works',
            'hse_audit_settings', 'hse_inspection_settings', 'hse_training_settings',
        ];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

    }
}
