<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;

/**
 * Replacement-leave form options.
 *
 * Centralizes the option-list queries that previously lived inside the Blade
 * views (M2: business logic out of blades) — controllers hand these to the
 * create/edit forms.
 */
class ReplacementLeaveService
{
	/**
	 * Active staff eligible for replacement leave (division 2 excluded).
	 */
	public function staffOptions(): Collection
	{
		return Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
			->where('staffs.active', 1)
			->where('logins.active', 1)
			->where(function ($query) {
				$query->where('staffs.div_id', '!=', 2)
				->orWhereNull('staffs.div_id');
			})
			->select('staffs.id as staffID', 'staffs.*', 'logins.*')
			->orderBy('logins.username', 'asc')
			->get();
	}

	/**
	 * Customer dropdown options.
	 *
	 * @param  bool  $sortKeys  sort by customer id (the edit form sorts keys)
	 */
	public function customerOptions(bool $sortKeys = false): array
	{
		$options = Customer::pluck('customer', 'id');

		return ($sortKeys ? $options->sortKeys() : $options)->toArray();
	}
}
