<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

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

	/**
	 * Decorate replacement-leave rows for the index table: username, formatted
	 * dates and the linked HR9 leave refs (were inline queries + Carbon in the
	 * blade).
	 */
	public function indexRows(Collection $replacements): Collection
	{
		return $replacements->map(function ($replacement) {
			$replacement->username = $replacement->belongstostaff?->hasmanylogin()?->where('active', 1)->first()?->username;
			$replacement->date_start_fmt = Carbon::parse($replacement->date_start)->format('j M Y');
			$replacement->date_end_fmt = Carbon::parse($replacement->date_end)->format('j M Y');

			$replacement->leave_refs = $replacement->belongstomanyleave()->get()->map(function ($val) {
				return '<a href="' . route('hrleave.show', $val->id) . '">HR9-' . str_pad($val->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $val->leave_year . '</a><br />';
			});

			return $replacement;
		});
	}
}
