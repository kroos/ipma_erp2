<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\OptDayType;
use App\Models\Staff;
use Illuminate\Database\Seeder;

/**
 * Idempotent sample data for manual QA.
 *
 * Safe to run against the populated dev DB — every insert is keyed off a
 * recognizable "SAMPLE" marker, so re-running never duplicates rows.
 *
 * Manual run only: deliberately NOT registered in DatabaseSeeder, so fresh
 * installs are not polluted with QA sample data.
 *
 *   php artisan db:seed --class=SampleDataSeeder
 */
class SampleDataSeeder extends Seeder
{
    private const CUSTOMER_NAME = 'SAMPLE - QA Customer';

    public function run(): void
    {
        // 1. sample customer
        $customer = Customer::firstOrCreate(
            ['customer' => self::CUSTOMER_NAME],
            [
                'contact' => 'QA Sample Contact',
                'address' => 'Sample address for manual QA',
                'remarks' => 'Created by SampleDataSeeder for manual QA',
            ]
        );

        // 2. sample outstation for the first active staff
        $staff = Staff::query()->where('active', 1)->first();

        if (!$staff) {
            $this->warn('No active staff found — skipping sample outstation & attendance.');

            return;
        }

        $outstation = HROutstation::firstOrCreate(
            [
                'staff_id' => $staff->id,
                'customer_id' => $customer->id,
                'date_from' => now()->format('Y-m-d'),
                'date_to' => now()->addDay()->format('Y-m-d'),
            ],
            [
                'active' => 1,
                'remarks' => 'Created by SampleDataSeeder for manual QA',
            ]
        );		// 3. optional attendance row for the same staff/date (daytype_id is a NOT NULL FK)
		try {
			$daytypeId = OptDayType::query()->value('id');

			if (!$daytypeId) {
				throw new \RuntimeException('no option_daytypes row available');
			}

			HRAttendance::firstOrCreate(
				[
					'staff_id' => $staff->id,
					'attend_date' => now()->format('Y-m-d'),
				],
				[
					'daytype_id' => $daytypeId,
					'remarks' => 'SAMPLE - created by SampleDataSeeder',
				]
			);
		} catch (\Throwable $e) {
			$this->warn('Attendance sample skipped (' . $e->getMessage() . ')');
		}

        $this->info('Sample data ready: customer #' . $customer->id . ' / outstation #' . $outstation->id . ' / staff #' . $staff->id . '.');
    }

    private function info(string $message): void
    {
        if ($this->command) {
            $this->command->info($message);
        }
    }

    private function warn(string $message): void
    {
        if ($this->command) {
            $this->command->warn($message);
        }
    }
}
