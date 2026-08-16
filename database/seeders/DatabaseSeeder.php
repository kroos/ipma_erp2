<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CiTableSeeder::class,
            CsOrderTableSeeder::class,
            HrAppraisalTableSeeder::class,
            IcsTableSeeder::class,
            OptionTableSeeder1::class,
            OptionTableSeeder2::class,
            SettingsTableSeeder::class,
            HrStaffTableSeeder::class,
            HrTableSeeder::class,
            HrLeaveTableSeeder::class,
            SalesTableSeeder::class,
            PivotTableSeeder::class,
            QuotTableSeeder::class,
            MiscTableSeeder::class,
        ]);
    }
}
