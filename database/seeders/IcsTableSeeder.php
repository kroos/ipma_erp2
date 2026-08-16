<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IcsTableSeeder extends Seeder
{
    /**
     * Seed the application's database with exact data from ipmaerp.sql.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('ics_floatth_constant')->truncate();
        DB::table('ics_food_rates')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- ics_floatth_constant (1 rows) ----
        DB::unprepared('INSERT INTO `ics_floatth_constant` VALUES
(1, 0.12500, 1.50000, 100, 0.80000, 0.12500, 1, \'All The Constant And The Rate\', \'2018-11-27 16:48:10\', \'2019-03-05 16:27:27\');');

        // ---- ics_food_rates (5 rows) ----
        DB::unprepared('INSERT INTO `ics_food_rates` VALUES
(1, \'RM 30\', 30, NULL, \'2018-11-26 14:46:05\', \'2018-11-26 14:46:05\'),
(2, \'RM 25\', 25, NULL, \'2018-11-26 14:46:05\', \'2018-11-26 14:46:05\'),
(3, \'RM 20\', 20, NULL, \'2018-11-26 14:46:05\', \'2018-11-26 14:46:05\'),
(4, \'RM 15\', 15, NULL, \'2018-11-26 14:46:05\', \'2018-11-26 14:46:05\'),
(5, \'RM 0\', 0, NULL, \'2018-11-26 14:46:05\', \'2018-11-26 14:46:05\');');

    }
}
