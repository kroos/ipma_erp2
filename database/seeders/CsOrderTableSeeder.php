<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CsOrderTableSeeder extends Seeder
{
    /**
     * Seed the application's database with exact data from ipmaerp.sql.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('cs_order_deliveries')->truncate();
        DB::table('cs_order_item_statuses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- cs_order_deliveries (7 rows) ----
        DB::unprepared('INSERT INTO `cs_order_deliveries` VALUES
(1, \'Courier\', NULL, \'2019-03-12 14:08:34\', \'2019-03-12 14:08:34\'),
(2, \'Company Vehicle\', NULL, \'2019-03-12 14:08:34\', \'2019-03-12 14:08:34\'),
(3, \'Container\', NULL, \'2019-03-12 14:08:34\', \'2019-03-12 14:08:34\'),
(4, \'Own Collection\', NULL, \'2019-03-12 14:08:34\', \'2019-03-12 14:08:34\'),
(5, \'Transport Agent\', NULL, \'2019-03-12 14:08:34\', \'2019-03-12 14:08:34\'),
(6, \'Special Delivery Instruction\', NULL, \'2019-03-12 14:08:34\', \'2019-03-12 14:08:34\'),
(8, \'Share Trip\', NULL, NULL, NULL);');

        // ---- cs_order_item_statuses (3 rows) ----
        DB::unprepared('INSERT INTO `cs_order_item_statuses` VALUES
(1, \'Order\', NULL, \'2019-03-09 09:23:46\', \'2019-03-09 09:23:46\'),
(2, \'Fabricate\', NULL, \'2019-03-09 09:23:46\', \'2019-03-09 09:23:46\'),
(3, \'Delivery\', NULL, \'2019-03-09 09:23:46\', \'2019-03-09 09:23:46\');');

    }
}
