<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\HumanResources\OptDisciplinaryAction;
use App\Models\HumanResources\OptViolation;
use App\Models\HumanResources\OptInfractions;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\OptTcms;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Discipline form options.
 *
 * Centralizes the option-list queries that previously lived inside the Blade
 * views (M2: business logic out of blades) — controllers hand these arrays to
 * the create/edit forms.
 */
class DisciplineService
{
	/**
	 * Option arrays shared by the discipline create/edit forms.
	 *
	 * @return array{staff: array, disciplinary_action: array, violation: array, infraction: array}
	 */
	/**
	 * Absent report for the discipline absent screen (was built with nested
	 * inline queries inside the blade). Groups attendance rows by year, then by
	 * staff, decorating each row's display values + the per-staff absent-day
	 * total.
	 *
	 * @return array  [{ ayear, staffs: [{ username, name, rows: [...], dur }] }]
	 */	public function absentReport(): array
	{
		$years = HRAttendance::groupByRaw('YEAR(attend_date)')
			->selectRaw('YEAR(attend_date) as ayear')
			->whereIn('attendance_type_id', [1, 2])
			->orderBy('ayear', 'DESC')
			->get();

		$leaveShort = OptTcms::pluck('leave_short', 'id')->toArray();

		return $years->map(function ($tp) use ($leaveShort) {
			$staffRows = HRAttendance::join('logins', 'hr_attendances.staff_id', '=', 'logins.staff_id')
				->where('logins.active', 1)
				->whereIn('attendance_type_id', [1, 2])
				->whereYear('attend_date', $tp->ayear)
				->groupBy('hr_attendances.staff_id')
				->orderBy('logins.username', 'ASC')
				->orderBy('attend_date', 'DESC')
				->get();

			$staffs = $staffRows->map(function ($value) use ($tp, $leaveShort) {
				$rows = HRAttendance::where('hr_attendances.staff_id', $value->staff_id)
					->whereIn('attendance_type_id', [1, 2])
					->whereYear('attend_date', $tp->ayear)
					->orderBy('attend_date', 'DESC')
					->get();

				$dur = 0;
				$decorated = $rows->map(function ($t) use ($leaveShort, &$dur) {
					$dur += ($t->attendance_type_id == 1) ? 1 : 0.5;

					$t->name = $t->belongstostaff?->name;
					$t->date_fmt = Carbon::parse($t->attend_date)->format('j M Y');
					$t->leave_short = $leaveShort[$t->attendance_type_id] ?? '';
					$t->leave_ref = $t->belongstoleave ? 'HR9-' . str_pad($t->belongstoleave->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $t->belongstoleave->leave_year : null;
					return $t;
				});

				return [
					'username' => $value->username,
					'name' => Staff::find($value->staff_id)?->name,
					'rows' => $decorated,
					'dur' => $dur,
				];
			});

			return ['ayear' => $tp->ayear, 'staffs' => $staffs];
		})->values()->all();
	}

	/**
	 * Option arrays shared by the discipline create/edit forms.
	 *
	 * @return array{staff: array, disciplinary_action: array, violation: array, infraction: array}
	 */
	public function formOptions(): array
	{
		$staff = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
			->select(DB::raw('CONCAT(username, " - ", name) AS display_name'), 'staffs.id')
			->where('staffs.active', 1)
			->where('logins.active', 1)
			->pluck('display_name', 'id')
			->toArray();

		$disciplinary_action = OptDisciplinaryAction::pluck('disciplinary_action', 'id')->toArray();

		$violation = OptViolation::select(DB::raw('CONCAT(IFNULL(violation, ""), " - ", IFNULL(remarks, "")) AS display_violation'), 'id')
			->pluck('display_violation', 'id')
			->toArray();

		$infraction = OptInfractions::select(DB::raw('CONCAT(IFNULL(infraction, ""), " - ", IFNULL(remarks, "")) AS display_infraction'), 'id')
			->pluck('display_infraction', 'id')
			->toArray();

		return compact('staff', 'disciplinary_action', 'violation', 'infraction');
	}
}
