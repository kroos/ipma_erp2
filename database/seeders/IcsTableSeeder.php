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
        DB::table('ics_accommodation_rates')->truncate();
        DB::table('ics_categories')->truncate();
        DB::table('ics_charges')->truncate();
        DB::table('ics_floatth_constant')->truncate();
        DB::table('ics_food_rates')->truncate();
        DB::table('ics_machine_models')->truncate();
        DB::table('ics_status')->truncate();
        DB::table('ics_working_types')->truncate();
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

        // ---- ics_accommodation_rates (3 rows) ----
        DB::unprepared('INSERT INTO `ics_accommodation_rates` VALUES
(1, \'Full Day\', 1, NULL, \'2018-11-28 08:53:23\', \'2018-11-28 08:53:23\'),
(2, \'Half Day\', 0.5, NULL, \'2018-11-28 08:53:23\', \'2018-11-28 08:53:23\'),
(3, \'None\', 0, NULL, \'2018-11-28 08:53:23\', \'2018-11-28 08:53:23\');');

        // ---- ics_categories (3 rows) ----
        DB::unprepared('INSERT INTO `ics_categories` VALUES
(1, \'Service & Repair\', NULL, \'2018-12-24 08:54:37\', \'2018-12-24 08:54:37\'),
(2, \'New Installation\', NULL, \'2018-12-24 08:54:37\', \'2018-12-24 08:54:37\'),
(3, \'New Installation, Service & Repair\', NULL, \'2018-12-24 08:54:37\', \'2018-12-24 08:54:37\');');

        // ---- ics_charges (2 rows) ----
        DB::unprepared('INSERT INTO `ics_charges` VALUES
(1, \'Charge Parts\', NULL, \'2018-11-21 09:24:34\', \'2018-11-21 09:24:34\'),
(2, \'Full Charge\', NULL, \'2018-11-21 09:24:34\', \'2018-11-21 09:24:34\');');

        // ---- ics_machine_models (39 rows) ----
        DB::unprepared('INSERT INTO `ics_machine_models` VALUES
(1, \'AUTO HULLER AH-10\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(2, \'AUTO HULLER AH-250\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(3, \'BUCKET ELEVATOR E-6\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(4, \'BUCKET ELEVATOR E-8\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(5, \'BUCKET ELEVATOR E-10\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(6, \'CHAIN CONVEYOR CC-300\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(7, \'CHAIN ELECON EC-30\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(8, \'DESTONER DS-50\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(9, \'DESTONER DS-75\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(10, \'HORIZONTAL ELECON EH-30\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(11, \'HORIZONTAL ELECON EH-12\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(12, \'VERTICAL ELECON EV-30\', NULL, \'2018-11-22 15:23:42\', \'2018-11-22 15:23:42\'),
(13, \'VERTICAL ELECON EV-12\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(14, \'HAMMER MILL \', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(15, \'HUSK SEPARATOR HS-200\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(16, \'INDENTED CYLINDER GRADER CG-600\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(17, \'INDENTED CYLINDER GRADER ICG-1200\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(18, \'INLINE FLOW WEIGHER IFW-50\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(19, \'INLINE FLOW WEIGHER IFW-15\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(20, \'LAB. CYLINDER GRADER LCG-250\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(21, \'PRECLEANER PC-70\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(22, \'PRECLEANER SAB-800\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(23, \'PRECLEANER SAB-1000\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(24, \'PRECLEANER SAB-1250\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(25, \'PADDY SEPARATOR PS-724\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(26, \'RICE POLISHER RP-100\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(27, \'RICE WHITENER RW-18\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(28, \'ROTARY SIFTER RS-200\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(29, \'AUTO PACKER RPM-100\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(30, \'ROTARY VALVE RV-40-25\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(31, \'ROTARY VALVE RV-40-45\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(32, \'SEMI AUTO PACKER SAP-50\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(33, \'ROTARY PACKER RTP-1000\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(34, \'INCLINE MINI CONVEYOR IMC-400\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(35, \'INLINE FLOW WEIGHER(IFW-50-TP)\', NULL, \'2018-11-22 15:23:43\', \'2018-11-22 15:23:43\'),
(36, \'RPM-100\', NULL, \'2019-01-30 10:53:16\', \'2019-01-30 10:53:16\'),
(37, \'AH-10\', NULL, \'2019-01-30 10:54:09\', \'2019-01-30 10:54:09\'),
(38, \'RP-100\', NULL, \'2019-01-30 10:54:43\', \'2019-01-30 10:54:43\'),
(41, \'1 TON WEIGHER\', NULL, \'2019-02-19 11:13:18\', \'2019-02-19 11:13:18\');');

        // ---- ics_status (2 rows) ----
        DB::unprepared('INSERT INTO `ics_status` VALUES
(1, \'Close\', NULL, \'2018-12-24 11:50:00\', \'2018-12-24 11:50:00\'),
(2, \'Need To Follow Up The Customer\', NULL, \'2018-12-24 11:50:00\', \'2018-12-24 11:50:00\');');

        // ---- ics_working_types (2 rows) ----
        DB::unprepared('INSERT INTO `ics_working_types` VALUES
(1, \'Full Day\', 1, NULL, \'2018-11-26 14:46:05\', \'2018-11-26 14:46:05\'),
(2, \'Half Day\', 2, NULL, \'2018-11-26 14:46:05\', \'2018-11-26 14:46:05\');');

    }
}
