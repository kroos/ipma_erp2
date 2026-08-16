<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\HumanResources\HROvertimeRange;
use Illuminate\Support\Carbon;

/**
 * Overtime form data.
 *
 * Centralizes the role/permission tree and the option queries that previously
 * lived inside the Blade views (M2: business logic out of blades).
 */
class OvertimeService
{
	/**
	 * Data for the overtime create form: the staff list this user is allowed to
	 * assign overtime to, plus the auth-derived flags the page's JS needs.
	 *
	 * @return array{staffs: \Illuminate\Support\Collection, staffId: int, hasMinDate: bool}
	 */
	public function createData(): array
	{
		$me = auth()->user()->belongstostaff;

		$me1 = $me->div_id == 1;          // hod
		$me2 = $me->div_id == 5;          // hod assistant
		$me3 = $me->div_id == 4;          // supervisor
		$me5 = $me->authorise_id == 1;    // admin
		$me6 = $me->div_id == 2;          // director
		$dept = $me->belongstomanydepartment()->wherePivot('main', 1)->first();
		$deptid = $dept->id;
		$branch = $dept->branch_id;

		$staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
			->where('staffs.active', 1)
			->where('logins.active', 1)
			->where(function ($query) {
				$query->where('staffs.div_id', '!=', 2)
				->orWhereNull('staffs.div_id');
			})
			->select('staffs.id as staffID', 'staffs.*', 'logins.username')
			->orderBy('logins.username', 'asc')
			->get();

		// keep only the staff this user may assign overtime to (the previous
		// inline permission tree lived in the blade — moved here verbatim)
		$staffs = $staffs->filter(function ($k) use ($me1, $me2, $me3, $me5, $me6, $deptid, $branch) {
			$mainDept = $k->belongstomanydepartment()?->wherePivot('main', 1)->first();
			$ha = false;

			if ($me1) {                                          // hod
				if ($deptid == 21 || $deptid == 28) {              // dept prod A / prod B
					$ha = $mainDept?->id == $deptid || $mainDept?->category_id == 2;
				} elseif ($deptid == 14) {                         // HR
					$ha = true;
				} elseif ($deptid == 6) {                          // cust serv
					$ha = $mainDept?->id == $deptid || $mainDept?->id == 7;
				} elseif ($deptid == 23) {                         // purchasing
					$ha = $mainDept?->id == $deptid || $mainDept?->id == 16 || $mainDept?->id == 17;
				} else {                                           // other dept
					$ha = $mainDept?->id == $deptid;
				}
			} elseif ($me2) {                                    // asst hod
				if ($deptid == 14) {                               // HR
					$ha = true;
				} elseif ($deptid == 6) {                          // cust serv
					$ha = $mainDept?->id == $deptid || $mainDept?->id == 7;
				}
			} elseif ($me3) {                                    // supervisor
				if ($branch == 1 || $branch == 2) {                // branch A / branch B
					$ha = $mainDept?->id == $deptid || ($mainDept?->category_id == 2 && $mainDept?->branch_id == $branch);
				}
			} elseif ($me6) {                                    // director
				$ha = true;
			} elseif ($me5) {                                    // admin
				$ha = true;
			}

			return $ha;
		});

		// flags the create page's JS reads (staffId + hasMinDate)
		$deptJs = $me->belongstomanydepartment()->where('main', 1)->first();

		return [
			'staffs' => $staffs,
			'staffId' => $me->id,
			'hasMinDate' => $me->div_id == 4 || ($me->div_id == 1 && $deptJs?->department_id == 21),
		];
	}

	/**
	 * Staff dropdown options for the edit form: id => 'username - name'.
	 */
	public function staffOptions(?int $includeStaffId = null): array
	{
		$options = Staff::where('active', 1)->get()->mapWithKeys(function ($s) {
			$username = $s->hasmanylogin()->where('active', 1)->first()?->username;

			return [$s->id => ($username ?? '') . ' - ' . $s->name];
		})->toArray();

		if ($includeStaffId && !isset($options[$includeStaffId])) {
			$s = \App\Models\Staff::find($includeStaffId);
			if ($s) {
				$username = $s->hasmanylogin()->where('active', 1)->first()?->username;
				$options[$includeStaffId] = ($username ?? '') . ' - ' . $s->name;
			}
		}

		return $options;
	}

	/**
	 * Overtime-range dropdown options for the edit form: id => 'start <=> end'.
	 */
	public function overtimeRangeOptions(): array
	{
		return HROvertimeRange::where('active', 1)->get()->mapWithKeys(function ($r) {
			return [$r->id => Carbon::parse($r->start)->format('g:i a') . ' <=> ' . Carbon::parse($r->end)->format('g:i a')];
		})->toArray();
	}
}
