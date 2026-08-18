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
	 * Overtime index list: keep only the rows the current user may see (same
	 * permission tree as createData) and decorate the display dates that the
	 * blade used to format inline with Carbon.
	 *
	 * @param  \Illuminate\Support\Collection  $overtimes  raw HROvertime rows
	 * @return \Illuminate\Support\Collection  allowed rows, each with username / ot_date_fmt / start_fmt / end_fmt
	 */
	public function indexData(\Illuminate\Support\Collection $overtimes): \Illuminate\Support\Collection
	{
		$me = auth()->user()->belongstostaff;

		$me1 = $me->div_id == 1;          // hod
		$me2 = $me->div_id == 5;          // hod assistant
		$me3 = $me->div_id == 4;          // supervisor
		$me5 = $me->authorise_id == 1;    // admin
		$me6 = $me->div_id == 2;          // director
		$dept = $me->belongstomanydepartment()->wherePivot('main', 1)->first();
		$deptid = $dept?->id;
		$branch = $dept?->branch_id;

		return $overtimes->filter(function ($key) use ($me1, $me2, $me3, $me5, $me6, $deptid, $branch) {
			$mainDept = $key->belongstostaff?->belongstomanydepartment()?->wherePivot('main', 1)->first();
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

			if (!$ha) {
				return false;
			}

			$key->username = $key->belongstostaff?->hasmanylogin()?->where('active', 1)->first()?->username;
			$key->ot_date_fmt = Carbon::parse($key->ot_date)->format('j M Y');
			$key->start_fmt = $key->belongstoovertimerange?->start ? Carbon::parse($key->belongstoovertimerange->start)->format('g:i a') : null;
			$key->end_fmt = $key->belongstoovertimerange?->end ? Carbon::parse($key->belongstoovertimerange->end)->format('g:i a') : null;
			return true;
		})->values();
	}

	/**
	 * Overtime report grid (index + printpdf shared). Precomputes the date
	 * columns, per-cell time/background, per-person totals and the grand total
	 * that the report blades used to compute inline with Carbon / ob_echo.
	 *
	 * @param  \Illuminate\Support\Collection|null  $overtimes  report rows (username/name/department/staff_id)
	 * @param  array  $otMap  staff_id => [date => HROvertime] lookup (from controller)
	 * @return array{columns: array, rows: array, total_col: int, grand_total: string, claim_form_title: string, current_year: int, last_year: int}
	 */
	public function reportData(?\Illuminate\Support\Collection $overtimes, array $otMap, ?string $dateStart, ?string $dateEnd): array
	{
		$currentYear = Carbon::now()->year;
		$lastYear = Carbon::now()->subYear()->year;

		$columns = [];
		if ($dateStart && $dateEnd) {
			$startDate = Carbon::parse($dateStart);
			$endDate = Carbon::parse($dateEnd);

			for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
				$columns[] = ['ymd' => $date->format('Y-m-d'), 'label' => $date->format('d/m')];
			}
		}

		$rows = [];
		$grandTotal = 0;
		$deptCache = [];

		foreach (($overtimes ?? collect()) as $overtime) {
			$cells = [];
			$personTotal = 0;

			foreach ($columns as $column) {
				$ot = $otMap[$overtime->staff_id][$column['ymd']] ?? null;
				$background = '';
				$timeFmt = '';

				if ($ot) {
					// grey out cells whose assigner is HR/cust-serv dept (14/15) — cached per overtime row
					$deptId = $deptCache[$ot->id] ?? null;
					if ($deptId === null) {
						$deptId = $ot->belongstoassignstaff?->belongstomanydepartment()?->first()?->department_id;
						$deptCache[$ot->id] = $deptId;
					}
					if ($deptId == '14' || $deptId == '15') {
						$background = 'background-color: #d9d9d9';
					}

					if ($ot->belongstoovertimerange?->total_time) {
						$timeFmt = Carbon::parse($ot->belongstoovertimerange->total_time)->format('H:i');
						$timeParts = explode(':', $timeFmt);
						$personTotal += (int) $timeParts[0] * 60 + (int) $timeParts[1];
					}
				}

				$cells[] = ['time' => $timeFmt, 'background' => $background];
			}

			$grandTotal += $personTotal;
			$rows[] = [
				'username' => $overtime->username,
				'name' => $overtime->name,
				'department' => $overtime->department,
				'cells' => $cells,
				'total' => sprintf('%02d', intdiv($personTotal, 60)) . ':' . sprintf('%02d', $personTotal % 60),
			];
		}

		return [
			'columns' => $columns,
			'rows' => $rows,
			'total_col' => count($columns),
			'grand_total' => sprintf('%02d', intdiv($grandTotal, 60)) . ':' . sprintf('%02d', $grandTotal % 60),
			'claim_form_title' => ($dateStart && $dateEnd)
				? 'Overtime Claim Form ' . Carbon::parse($dateStart)->format('j') . ' - ' . Carbon::parse($dateEnd)->format('j') . ' ' . Carbon::parse($dateEnd)->format('F') . ' ' . Carbon::parse($dateEnd)->format('Y')
				: '',
			'current_year' => $currentYear,
			'last_year' => $lastYear,
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
