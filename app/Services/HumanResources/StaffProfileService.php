<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptLeaveType;
use App\Models\HumanResources\OptWorkingHour;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Staff / profile show-screen domain queries — extracted from the
 * staff/show and profile/show blade files (M2 refactor).
 */
class StaffProfileService
{
	/**
	 * Leave entitlements used by the profile header.
	 * Preserves the original double-write of $mcupl exactly as the blades had it.
	 */
	public function leaveEntitlements(Staff $person): array
	{
		$annl = $person->hasmanyleaveannual()?->where('year', now()->format('Y'))->first();
		$mcel = $person->hasmanyleavemc()?->where('year', now()->format('Y'))->first();
		$matl = $person->hasmanyleavematernity()?->where('year', now()->format('Y'))->first();

		$replt = $person->hasmanyleavereplacement()?->selectRaw('SUM(leave_total) as total')
			->where(function (Builder $query) {
				$query->whereDate('date_start', '>=', now()->startOfYear())
					->whereDate('date_end', '<=', now()->endOfYear());
			})
			->get();

		$replb = $person->hasmanyleavereplacement()?->selectRaw('SUM(leave_balance) as total')
			->where(function (Builder $query) {
				$query->whereDate('date_start', '>=', now()->startOfYear())
					->whereDate('date_end', '<=', now()->endOfYear());
			})
			->get();

		$upal = $person->hasmanyleave()?->selectRaw('SUM(period_day) as total')
			->where(function (Builder $query) {
				$query->whereDate('date_time_start', '>=', now()->startOfYear())
					->whereDate('date_time_end', '<=', now()->endOfYear());
			})
			->where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])
					->orWhereNull('leave_status_id');
			})
			->whereIn('leave_type_id', [3, 6])
			->get();

		$mcupl = $person->hasmanyleave()?->selectRaw('SUM(period_day) as total')
			->where(function (Builder $query) {
				$query->whereDate('date_time_start', '>=', now()->startOfYear())
					->whereDate('date_time_end', '<=', now()->endOfYear());
			})
			->where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])
					->orWhereNull('leave_status_id');
			})
			->where('leave_type_id', 11)
			->get();

		// original blade overwrote $mcupl with the raw collection — preserve that
		$mcupl = $person->hasmanyleave()?->get();

		return compact('annl', 'mcel', 'matl', 'replt', 'replb', 'upal', 'mcupl');
	}

	/**
	 * Family / auth data for the profile identity section.
	 */
	public function familyAndAuth(Staff $person): array
	{
		$login = $person->hasmanylogin()->where('active', '1')->first();
		$spouses = $person->hasmanyspouse()->get();
		$childrens = $person->hasmanychildren()->get();
		$emergencies = $person->hasmanyemergency()->get();

		return compact('login', 'spouses', 'childrens', 'emergencies');
	}

	/**
	 * Cross-backup partners (to / from).
	 */
	public function crossBackup(Staff $person): array
	{
		$cb = $person->crossbackupto()->get();
		$cbf = $person->crossbackupfrom()->get();

		return compact('cb', 'cbf');
	}

	/**
	 * Attendance year / month filter options.
	 */
	public function attendanceYears(Staff $person): array
	{
		$group_year = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
			->select(DB::raw('YEAR(hr_attendances.attend_date) AS year'))
			->where('hr_attendances.staff_id', $person->id)
			->groupBy('year')
			->orderBy('year', 'desc')
			->pluck('year', 'year')
			->toArray();

		$group_month = ['01' => '01', '02' => '02', '03' => '03', '04' => '04', '05' => '05', '06' => '06', '07' => '07', '08' => '08', '09' => '09', '10' => '10', '11' => '11', '12' => '12'];

		return compact('group_year', 'group_month');
	}

	/**
	 * Per-row display data for the attendance table, pre-computed so the view
	 * never queries. Keyed by attendance id.
	 */
	public function attendanceDecor(\Illuminate\Support\Collection $attendance, string $whGroup): array
	{
		$companyHours = [];
		$daytypes = [];
		$outstations = [];
		$overtimes = [];
		$leaveInfos = [];

		foreach ($attendance as $attend) {
			$date_name = Carbon::parse($attend->attend_date)->format('l');

			if ($whGroup == '0' && $date_name == 'Friday') {
				$company_hour = OptWorkingHour::where('option_working_hours.group', '=', $whGroup)
					->where('option_working_hours.effective_date_start', '<=', $attend->attend_date)
					->where('option_working_hours.effective_date_end', '>=', $attend->attend_date)
					->where('option_working_hours.category', '=', 3)
					->select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')
					->first();
			} elseif ($whGroup == '0') {
				$company_hour = OptWorkingHour::where('option_working_hours.group', '=', $whGroup)
					->where('option_working_hours.effective_date_start', '<=', $attend->attend_date)
					->where('option_working_hours.effective_date_end', '>=', $attend->attend_date)
					->where('option_working_hours.category', '!=', 3)
					->select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')
					->first();
			} else {
				$company_hour = OptWorkingHour::where('option_working_hours.group', '=', $whGroup)
					->where('option_working_hours.effective_date_start', '<=', $attend->attend_date)
					->where('option_working_hours.effective_date_end', '>=', $attend->attend_date)
					->where('option_working_hours.category', '=', 8)
					->select('time_start_am', 'time_end_am', 'time_start_pm', 'time_end_pm')
					->first();
			}

			$companyHours[$attend->id] = $company_hour;
			$daytypes[$attend->id] = $attend->belongstodaytype()->first();
			$outstations[$attend->id] = $attend->belongstooutstation?->belongstocustomer?->customer;
			$overtimes[$attend->id] = $attend->belongstoovertime?->belongstoovertimerange?->total_time;

			$leaveInfos[$attend->id] = null;
			if ($attend->leave_id != null && $attend->leave_id != '') {
				$leave_temp1 = $attend->belongstoleave()->first();
				$leave_temp2 = $attend->belongstoleave->belongstooptleavetype()->first();

				$leaveInfos[$attend->id] = [
					'id' => $leave_temp1->id,
					'form' => 'HR9-' . str_pad($leave_temp1->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $leave_temp1->leave_year,
					'type' => $leave_temp2->leave_type_code,
				];
			}
		}

		return compact('companyHours', 'daytypes', 'outstations', 'overtimes', 'leaveInfos');
	}

	/**
	 * Entitlement tables (annual / MC / maternity) plus the per-year leave
	 * maps used by each table's "Leave" column.
	 */
	public function leaveTables(Staff $person): array
	{
		$annualLeaves = $person->hasmanyleaveannual()->orderBy('year', 'DESC')->get();
		$mcLeaves = $person->hasmanyleavemc()->orderBy('year', 'DESC')->get();
		$maternityLeaves = $person->hasmanyleavematernity()->orderBy('year', 'DESC')->get();

		$annualMap = [];
		foreach ($annualLeaves as $al) {
			$annualMap[$al->year] = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
				->where('staff_id', $person->id)
				->whereYear('date_time_start', $al->year)
				->whereIn('leave_type_id', [1, 5])
				->get();
		}

		$mcMap = [];
		foreach ($mcLeaves as $al) {
			$mcMap[$al->year] = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
				->where('staff_id', $person->id)
				->whereYear('date_time_start', $al->year)
				->where('leave_type_id', 2)
				->get();
		}

		// maternity has no year filter in the original view — single shared list
		$maternity = HRLeave::where(function (Builder $query) {
			$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
		})
			->where('staff_id', $person->id)
			->where('leave_type_id', 7)
			->get();

		return compact('annualLeaves', 'mcLeaves', 'maternityLeaves', 'annualMap', 'mcMap', 'maternity');
	}

	/**
	 * Replacement leave table + per-row leave map (profile screen only).
	 */
	public function replacementTable(Staff $person): array
	{
		$replacementLeaves = $person->hasmanyleavereplacement()->orderBy('date_start', 'DESC')->get();

		$replacementMap = [];
		foreach ($replacementLeaves as $al) {
			$replacementMap[$al->id] = $al->belongstomanyleave()->where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})->get();
		}

		return compact('replacementLeaves', 'replacementMap');
	}

	/**
	 * Unpaid leave + MC-unpaid leave listings.
	 */
	public function unpaidLeaves(Staff $person): array
	{
		$leavesupls = HRLeave::where(function (Builder $query) {
			$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
		})
			->where('staff_id', $person->id)
			->whereIn('leave_type_id', [3, 6, 12])
			->get();

		$leavesmcs = HRLeave::where(function (Builder $query) {
			$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
		})
			->where('staff_id', $person->id)
			->where('leave_type_id', 11)
			->get();

		return compact('leavesupls', 'leavesmcs');
	}  /**
   * Full leave-record list for the "Leave" table (staff screen only).
   */
  public function leaveRecords(Staff $person): array
  {
    $leave_records = HRLeave::where('staff_id', $person->id)
      ->orderBy('date_time_start', 'DESC')
      ->orderBy('leave_type_id', 'ASC')
      ->orderBy('leave_status_id', 'DESC')
      ->get();

    return compact('leave_records');
  }

  /**
   * Leave-type code map used by the unpaid-leave tables.
   */
  public function leaveTypeMap(): array
  {
    return OptLeaveType::pluck('leave_type_code', 'id')->toArray();
  }

  /**
   * Disciplinary records table (staff screen only).
   */
  public function disciplinaryRecords(Staff $person): array
  {
    $disciplinaries = $person->hasmanyhrdisciplinary()->orderBy('misconduct_date', 'DESC')->get();

    return compact('disciplinaries');
  }
}
