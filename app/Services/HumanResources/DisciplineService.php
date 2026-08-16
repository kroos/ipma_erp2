<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\HumanResources\OptDisciplinaryAction;
use App\Models\HumanResources\OptViolation;
use App\Models\HumanResources\OptInfractions;
use Illuminate\Support\Facades\DB;

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
