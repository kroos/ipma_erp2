<?php

namespace App\Services\HumanResources;

use App\Models\Customer;
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HRLeaveReplacement;
use App\Models\HumanResources\OptLeaveType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * Entitlement list data for the shared entitlement index view (M6).
 *
 * The per-type controllers build their config and hand it — together with the
 * rows returned here — to the single generic
 * `humanresources.hrdept.entitlement.index` blade. The blade only renders;
 * it contains no DB queries.
 */
class EntitlementService
{
	/**
	 * Entitlement rows (annual / medical certificate / maternity) grouped by year.
	 *
	 * Each year yields an "active" and an "inactive" staff table.
	 *
	 * @param  class-string  $model  HRLeaveAnnual | HRLeaveMC | HRLeaveMaternity
	 * @param  string  $field  column prefix, e.g. "annual_leave"
	 */
	public function rows(string $model, string $field): array
	{
		$years = $model::groupBy('year')->select('year')->orderBy('year', 'DESC')->get();

		$rows = [];
		foreach ($years as $year) {
			$active = [];
			$inactive = [];

			$records = $model::where('year', $year->year)->orderBy('year', 'DESC')->get();
			foreach ($records as $record) {
				$isActive = (int) $record->belongstostaff?->active === 1;

				$leaves = $this->approvedLeaves($record);

				$row = [
					'username' => $record->belongstostaff?->hasmanylogin()
									?->when($isActive, fn (Builder $query) => $query->where('active', 1))
									?->first()?->username,
					'name' => $record->belongstostaff?->name,
					'leave' => $record->{$field},
					'adjustment' => $record->{$field . '_adjustment'},
					'utilize' => $record->{$field . '_utilize'},
					'balance' => $record->{$field . '_balance'},
					'remarks' => $record->remarks,
					'leaves' => $leaves,
					'leaves_total' => $leaves->sum('period_day'),
				];

				if ($isActive) {
					$active[] = $row;
				} else {
					$inactive[] = $row;
				}
			}

			$rows[$year->year] = compact('active', 'inactive');
		}

		return $rows;
	}

	/**
	 * Unpaid leave rows (unpaid / unpaid medical certificate) grouped by year,
	 * then by staff.
	 *
	 * @param  array  $leaveTypeIds  leave_type_id filter for the leave type
	 */
	public function uplRows(array $leaveTypeIds): array
	{
		$years = HRLeave::groupByRaw('YEAR(date_time_start)')
						->selectRaw('YEAR(date_time_start) as ryear')
						->whereIn('leave_type_id', $leaveTypeIds)
						->where(fn (Builder $query) => $this->approvedStatuses($query))
						->orderBy('ryear', 'DESC')
						->get();

		$rows = [];
		foreach ($years as $year) {
			$staffs = HRLeave::join('logins', 'hr_leaves.staff_id', '=', 'logins.staff_id')
							->whereIn('leave_type_id', $leaveTypeIds)
							->whereYear('date_time_start', $year->ryear)
							->where(fn (Builder $query) => $this->approvedStatuses($query))
							->groupBy('hr_leaves.staff_id')
							->orderBy('logins.username', 'ASC')
							->get();

			$staffGroups = [];
			foreach ($staffs as $staff) {
				$leaves = HRLeave::whereIn('leave_type_id', $leaveTypeIds)
								->whereYear('date_time_start', $year->ryear)
								->where(fn (Builder $query) => $this->approvedStatuses($query))
								->where('staff_id', $staff->staff_id)
								->orderBy('hr_leaves.date_time_start', 'DESC')
								->get();

				$items = [];
				$total = 0;
				foreach ($leaves as $leave) {
					$items[] = [
						'username' => $leave->belongstostaff?->hasmanylogin()->where('active', 1)->first()?->username,
						'name' => $leave->belongstostaff?->name,
						'id' => $leave->id,
						'leave_no' => $leave->leave_no,
						'leave_year' => $leave->leave_year,
						'leave_type_code' => OptLeaveType::find($leave->leave_type_id)?->leave_type_code,
						'period_day' => $leave->period_day,
						'from' => Carbon::parse($leave->date_time_start)->format('j M Y'),
						'to' => Carbon::parse($leave->date_time_end)->format('j M Y'),
						'reason' => $leave->reason,
					];
					$total += (float) $leave->period_day;
				}

				$staffGroups[] = [
					'username' => $staff->username,
					'staff_name' => Staff::find($staff->staff_id)?->name,
					'items' => $items,
					'total' => $total,
				];
			}

			$rows[$year->ryear] = $staffGroups;
		}

		return $rows;
	}

	/**
	 * Replacement leave rows grouped by year, then by staff.
	 */
	public function replacementRows(): array
	{
		$years = HRLeaveReplacement::groupByRaw('YEAR(date_start)')
									->selectRaw('YEAR(date_start) as ryear')
									->orderBy('ryear', 'DESC')
									->get();

		$rows = [];
		foreach ($years as $year) {
			$staffs = HRLeaveReplacement::join('logins', 'hr_leave_replacements.staff_id', '=', 'logins.staff_id')
										->whereYear('date_start', $year->ryear)
										->groupBy('hr_leave_replacements.staff_id')
										->orderBy('logins.username', 'ASC')
										->orderBy('hr_leave_replacements.date_start', 'DESC')
										->get();

			$staffGroups = [];
			foreach ($staffs as $staff) {
				$records = HRLeaveReplacement::whereYear('date_start', $year->ryear)
											->where('staff_id', $staff->staff_id)
											->orderBy('date_start', 'DESC')
											->get();

				$items = [];
				$total = 0;
				foreach ($records as $record) {
					$leaves = $this->approvedLeaves($record);

					$items[] = [
						'username' => $record->belongstostaff?->hasmanylogin()->where('active', 1)->first()?->username,
						'name' => $record->belongstostaff?->name,
						'reason' => $record->reason,
						'customer' => Customer::find($record->customer_id)?->customer,
						'leave_total' => $record->leave_total,
						'leave_utilize' => $record->leave_utilize,
						'leave_balance' => $record->leave_balance,
						'remarks' => $record->remarks,
						'leaves' => $leaves,
						'leaves_total' => $leaves->sum('period_day'),
					];
					$total += (float) $record->leave_balance;
				}

				$staffGroups[] = [
					'username' => $staff->username,
					'staff_name' => Staff::find($staff->staff_id)?->name,
					'items' => $items,
					'total' => $total,
				];
			}

			$rows[$year->ryear] = $staffGroups;
		}

		return $rows;
	}

	/**
	 * BelongsToMany leaves that are approved (or unstated) for the record.
	 */
	private function approvedLeaves($record): Collection
	{
		return $record->belongstomanyleave()
						->where(fn (Builder $query) => $this->approvedStatuses($query))
						->get();
	}

	/**
	 * Status filter used across the entitlement list queries.
	 */
	private function approvedStatuses(Builder $query): Builder
	{
		return $query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
	}
}
