<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuotTableSeeder extends Seeder
{
    /**
     * Seed the application's database with exact data from ipmaerp.sql.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('quot_item_attributes')->truncate();
        DB::table('quot_items')->truncate();
        DB::table('quot_uom')->truncate();
        DB::table('quot_warranties')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- quot_item_attributes (11 rows) ----
        DB::unprepared('INSERT INTO `quot_item_attributes` VALUES
(1, \'Model\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(2, \'Length\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(3, \'Height\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(4, \'Capacity\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(5, \'Platform\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(6, \'No. Of Channel\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(7, \'Reject Ratio\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(8, \'Remarks\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(9, \'Info\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(10, \'Image\', NULL, \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\'),
(11, NULL, \'a space to the item attributes\', \'2019-03-19 17:37:01\', \'2019-03-19 17:37:01\');');

        // ---- quot_items (3 rows) ----
        DB::unprepared('INSERT INTO `quot_items` VALUES
(1, \'AAA\', \'dunno yet\', 1.33, 1, NULL, \'2019-03-22 15:26:54\', \'2019-03-22 15:26:54\'),
(2, \'BBB\', \'2nd day\', 10.00, 1, NULL, \'2019-03-23 14:26:48\', \'2019-03-23 14:26:48\'),
(3, \'321321321321321312321\', NULL, 10000.00, 1, \'9000\', \'2019-04-17 17:05:31\', \'2019-04-17 17:05:31\');');

        // ---- quot_uom (4 rows) ----
        DB::unprepared('INSERT INTO `quot_uom` VALUES
(1, \'Unit\', NULL, \'2019-03-19 15:41:32\', \'2019-03-19 15:41:32\'),
(2, \'Lot\', NULL, \'2019-03-19 15:41:32\', \'2019-03-19 15:41:32\'),
(3, \'Set\', NULL, \'2019-03-19 15:41:32\', \'2019-03-19 15:41:32\'),
(4, \'Piece\', NULL, \'2019-03-19 15:41:32\', \'2019-03-19 15:41:32\');');

        // ---- quot_warranties (1 rows) ----
        DB::unprepared('INSERT INTO `quot_warranties` VALUES
(1, \'6 months warranty on machinery manufacturing faults only. Normal wear & tear, damage caused by improper usage, user negligence, unjustified exposure and wilful acts of user, electrical supply faults and other unforeseen circumstances are NOT INCLUDED in this warranty.\', NULL, \'2019-04-01 12:01:29\', \'2019-04-01 12:01:29\');');

    }
}
