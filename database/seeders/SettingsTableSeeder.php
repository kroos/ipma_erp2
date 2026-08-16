<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Seed the application's database with exact data from ipmaerp.sql.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ---- settings (7 rows) ----
        DB::unprepared('INSERT INTO `settings` VALUES
(1, \'Overlap Leave Date Setting\', \'HR\', NULL, \'Enable : Overlapped date is for staff to apply 2 times leave in 1 day\', \'2019-02-08 12:27:10\', \'2025-12-29 14:37:10\', NULL),
(2, \'Half Day MC Setting\', \'HR\', NULL, \'Enable : Can apply half day MC.\', \'2023-07-31 20:53:10\', \'2024-01-22 15:33:17\', NULL),
(3, \'Special Case Leave Setting\', \'HR\', NULL, \'Enable : S-UPL enable\', \'2023-07-13 15:29:52\', \'2023-07-13 15:29:52\', NULL),
(4, \'N Days Checking Setting\', \'HR\', 1, \'Enable : Cant apply leave within N days for AL, NRL & UPL\', \'2023-07-25 09:53:38\', \'2023-07-25 09:53:38\', NULL),
(5, \'Total Days For N Days Setting\', \'HR\', 3, \'Setting for on how many days from \\"N Days Checking Setting\\"\', \'2023-07-25 09:53:38\', \'2023-12-29 17:50:47\', NULL),
(6, \'Close for Next Year Leave\', \'HR\', 1, \'Enable : Block next year leave application\', \'2023-08-29 12:41:41\', \'2026-01-01 10:23:04\', NULL),
(7, \'Close for Last Year Leave\', \'HR\', NULL, \'Enable : Block last year leave application\', \'2024-01-02 15:15:22\', \'2026-01-01 10:22:21\', NULL);');

    }
}
