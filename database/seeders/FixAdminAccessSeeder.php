<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FixAdminAccessSeeder extends Seeder
{
    use WithoutModelEvents;

    private array $loginIds = [72, 117];

    public function run(): void
    {
        foreach ($this->loginIds as $loginId) {
            $login = DB::table('logins')->find($loginId);

            if (!$login || !$login->staff_id) {
                $this->command?->warn("Login #{$loginId}: record or staff_id missing, skipped.");

                continue;
            }

            $staff = DB::table('staffs')->find($login->staff_id);

            if (!$staff) {
                $this->command?->warn("Login #{$loginId}: staff #{$login->staff_id} not found, skipped.");

                continue;
            }

            if ((int) $staff->authorise_id === 1) {
                $this->command?->info("Login #{$loginId} -> staff #{$staff->id}: already Administrator, skipped.");

                continue;
            }

            if (!DB::table('option_authorities')->where('id', 1)->exists()) {
                $this->command?->warn("option_authorities #1 (Administrator) missing; cannot grant authorise_id=1.");

                continue;
            }

            DB::table('staffs')
                ->where('id', $staff->id)
                ->update(['authorise_id' => 1, 'updated_at' => now()]);

            $this->command?->info("Login #{$loginId} -> staff #{$staff->id}: granted Administrator access.");
        }
    }
}
