<?php
namespace App\Services\HumanResources;

// load models
use App\Models\Setting;
use App\Models\HumanResources\HRLeave;

// load array helper
use Illuminate\Support\Str;

// load Carbon
use Carbon\Carbon;

/**
 * HRLeaveService — "my leave" dashboard (humanresources.leave.index).
 *
 * Business logic extracted from the blade: profile-complete check, yearly
 * entitlements, unpaid/replacement balances, backup personnel, and the two
 * DataTable row builders (own leaves + backup approvals) served via API.
 */
class HRLeaveService
{
	public function __construct()
	{
	}

	/**
	 * Summary data for the dashboard top table.
	 */
	public function indexData(): array
	{
		$us = auth()->user()->belongstostaff;
		$year = now()->year;

		$settingStart = (int) Setting::find(7)?->active;   // 1 = current year only, else include previous year
		$settingEnd = (int) Setting::find(6)?->active;     // 1 = current year only, else include next year

		$startYear = ($settingStart == 1) ? $year : $year - 1;
		$endYear = ($settingEnd == 1) ? $year : $year + 1;
		$years = range($startYear, $endYear);

		$email = $us->email;
		$emer = $us->hasmanyemergency()?->get() ?? collect();

		$entitlements = [];
		foreach ($years as $i) {
			$al = $us->hasmanyleaveannual()?->where('year', $i)->first();
			$mc = $us->hasmanyleavemc()?->where('year', $i)->first();
			$ma = $us->hasmanyleavematernity()?->where('year', $i)->first();

			$entitlements[$i] = [
				'annual_init' => (float) ($al?->annual_leave ?? 0) + (float) ($al?->annual_leave_adjustment ?? 0),
				'annual_balance' => $al?->annual_leave_balance,
				'annual_low' => ($al?->annual_leave_balance ?? 0) < 4,
				'mc_init' => (float) ($mc?->mc_leave ?? 0) + (float) ($mc?->mc_leave_adjustment ?? 0),
				'mc_balance' => $mc?->mc_leave_balance,
				'mc_low' => ($mc?->mc_leave_balance ?? 0) < 4,
				'maternity_init' => (float) ($ma?->maternity_leave ?? 0) + (float) ($ma?->maternity_leave_adjustment ?? 0),
				'maternity_balance' => $ma?->maternity_leave_balance,
				'maternity_low' => ($ma?->maternity_leave_balance ?? 0) < 4,
			];
		}

		// unpaid leave for the current year (leave types 3 = UPL, 6 = EL-UPL)
		$unpaid = (float) ($us->hasmanyleave()?->whereYear('date_time_start', $year)->whereIn('leave_type_id', [3, 6])->get()->sum('period_day') ?? 0);

		// replacement leave: visibility uses filtered sum, displayed value is the unfiltered sum (as in the original view)
		$replacementVisible = ((float) $us->hasmanyleavereplacement()?->where('leave_balance', '<>', 0)->get()->sum('leave_balance') ?? 0) > 0;
		$replacement = (float) ($us->hasmanyleavereplacement()?->sum('leave_balance') ?? 0);

		$backupEnabled = $us->belongstoleaveapprovalflow?->backup_approval == 1;
		$backup = [];
		if ($backupEnabled) {
			foreach ($us->belongstomanydepartment()->get() as $de) {
				$staff = $de->belongstomanystaff()
					->where('active', 1)
					->where('staff_id', '<>', $us->id)
					->get()
					->sortBy('name')
					->pluck('name')
					->all();
				$backup[] = ['dept' => $de->name, 'staff' => array_values($staff)];
			}
			$cross = $us->crossbackupto()?->wherePivot('active', 1)->get()->pluck('name')->all() ?? [];
			if (!empty($cross)) {
				$backup[] = ['dept' => null, 'staff' => array_values($cross)];
			}
		}

		return [
			'years' => $years,
			'show_maternity' => $us->gender_id == 2,
			'profile_incomplete' => is_null($email) && $emer->isEmpty(),
			'profile_edit_url' => route('profile.edit', $us->id),
			'leave_create_url' => route('leave.create'),
			'entitlements' => $entitlements,
			'unpaid' => $unpaid,
			'replacement' => $replacement,
			'replacement_visible' => $replacementVisible,
			'backup_enabled' => $backupEnabled,
			'backup' => $backup,
		];
	}

	/**
	 * DataTable sources for the same page — own leaves + backup approvals.
	 * Served read-only via GET api/leave/my-leaves (client-side DataTables).
	 */
	public function myLeaves(): array
	{
		$us = auth()->user()->belongstostaff;

		$year = now()->year;
		$settingStart = (int) Setting::find(7)?->active;
		$settingEnd = (int) Setting::find(6)?->active;
		$beginy = ($settingStart == 1) ? $year : $year - 1;
		$endy = ($settingEnd == 1) ? $year : $year + 1;

		$lea = $us->hasmanyleave()
			->whereYear('date_time_start', '>=', $beginy)
			->whereYear('date_time_end', '<=', $endy)
			->with([
				'belongstooptleavetype',
				'belongstooptleavestatus',
				'hasmanyleaveapprovalbackup.belongstoleavestatus',
				'hasmanyleaveapprovalsupervisor.belongstoleavestatus',
				'hasmanyleaveapprovalhod.belongstoleavestatus',
				'hasmanyleaveapprovaldir.belongstoleavestatus',
				'hasmanyleaveapprovalhr.belongstoleavestatus',
			])
			->get();

		$leaves = [];
		foreach ($lea as $leav) {
			$period = $this->periodLabel($leav);
			$leaves[] = [
				'hr9' => '<a href="'.route('leave.show', $leav->id).'" alt="Print PDF" title="Print PDF" target="_blank">HR9-'.str_pad((string) $leav->leave_no, 5, '0', STR_PAD_LEFT).'/'.e((string) $leav->leave_year).'</a>',
				'applied' => Carbon::parse($leav->created_at)->format('j M Y'),
				'code' => e((string) $leav->belongstooptleavetype?->leave_type_code),
				'reason' => $this->reasonHtml($leav->reason),
				'from' => $period['from'],
				'to' => $period['to'],
				'period' => $period['period'],
				'verify' => e((string) $leav->verify_code),
				'approvals' => $this->approvalsHtml($leav),
				'status' => $this->statusHtml($leav),
			];
		}

		// rows where the current user acts as backup approver for someone else
		$x = $us->hasmanyleaveapprovalbackup()
			->whereNull('leave_status_id')
			->with([
				'belongstostaffleave.belongstostaff',
				'belongstostaffleave.belongstooptleavetype',
			])
			->get();

		$backups = [];
		foreach ($x as $a) {
			$leave = $a->belongstostaffleave;
			$period = $this->periodLabel($leave);

			$z = Carbon::parse(now())->daysUntil($leave->date_time_start, 1)->count();
			if (3 >= $z && $z >= 2) {
				$u = 'table-warning';
			} elseif ($z < 2) {
				$u = 'table-danger';
			} else {
				$u = null;
			}

			$backups[] = [
				'name' => '<a href="'.route('leave.show', $a->leave_id).'" class="btn btn-sm btn-outline-secondary" alt="Print PDF" title="Print PDF" target="_blank"><i class="far fa-file-pdf"></i></a>&nbsp;'.e((string) $leave->belongstostaff?->name),
				'code' => e((string) $leave->belongstooptleavetype?->leave_type_code),
				'reason' => $this->reasonHtml($leave->reason),
				'applied' => Carbon::parse($a->created_at)->format('j M Y'),
				'from' => $period['from'],
				'to' => $period['to'],
				'period' => $period['period'],
				'status' => '<a href="#" class="btn btn-sm btn-outline-secondary rapprover_btn" id="rapprover_btn_'.$a->id.'" data-id="'.$a->id.'" alt="Replacement Approver" title="Replacement Approver"><i class="bi bi-box-arrow-in-down"></i></a>',
				'row_class' => $u,
			];
		}

		return ['leaves' => $leaves, 'backups' => $backups];
	}

	/**
	 * From/To/Period labels for a leave row (half-day / hourly / full-day handling).
	 */
	private function periodLabel(HRLeave $leav): array
	{
		if ($leav->leave_type_id == 9 || ($leav->leave_type_id != 9 && $leav->half_type_id == 2) || ($leav->leave_type_id != 9 && $leav->half_type_id == 1)) {
			$dts = Carbon::parse($leav->date_time_start)->format('j M Y g:i a');
			$dte = Carbon::parse($leav->date_time_end)->format('j M Y g:i a');
			if ($leav->leave_type_id != 9) {
				$dper = $leav->period_day.' Day';
			} else {
				$i = Carbon::parse($leav->period_time);
				$dper = $i->hour.' hour, '.$i->minute.' minutes';
			}
		} else {
			$dts = Carbon::parse($leav->date_time_start)->format('j M Y');
			$dte = Carbon::parse($leav->date_time_end)->format('j M Y');
			$dper = $leav->period_day.' day/s';
		}

		return ['from' => $dts, 'to' => $dte, 'period' => $dper];
	}

	/**
	 * Truncated reason with a Bootstrap tooltip carrying the full text.
	 */
	private function reasonHtml(?string $reason): string
	{
		$reason = (string) $reason;

		return '<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="'.e($reason).'">'.e(Str::of($reason)->words(3, ' >')).'</span>';
	}

	/**
	 * Nested approval chain (Backup / Supervisor / HOD / Director / HR) for one leave.
	 */
	private function approvalsHtml(HRLeave $leav): string
	{
		$html = '<table class="table table-hover table-sm"><tbody>';

		if ($leav->hasmanyleaveapprovalbackup->isNotEmpty()) {
			$html .= '<tr><td>Backup</td><td>'.e((string) ($leav->hasmanyleaveapprovalbackup->first()->belongstoleavestatus?->status ?? 'Pending')).'</td></tr>';
		}
		if ($leav->hasmanyleaveapprovalsupervisor->isNotEmpty()) {
			$html .= '<tr><td>Supervisor</td><td>'.e((string) ($leav->hasmanyleaveapprovalsupervisor->first()->belongstoleavestatus?->status ?? 'Pending')).'</td></tr>';
		}
		if ($leav->hasmanyleaveapprovalhod->isNotEmpty()) {
			$html .= '<tr><td>HOD</td><td>'.e((string) ($leav->hasmanyleaveapprovalhod->first()->belongstoleavestatus?->status ?? 'Pending')).'</td></tr>';
		}
		if ($leav->hasmanyleaveapprovaldir->isNotEmpty()) {
			$html .= '<tr><td>Director</td><td>'.e((string) ($leav->hasmanyleaveapprovaldir->first()->belongstoleavestatus?->status ?? 'Pending')).'</td></tr>';
		}
		if ($leav->hasmanyleaveapprovalhr->isNotEmpty()) {
			$html .= '<tr><td>HR</td><td>'.e((string) ($leav->hasmanyleaveapprovalhr->first()->belongstoleavestatus?->status ?? 'Pending')).'</td></tr>';
		}

		$html .= '</tbody></table>';

		return $html;
	}

	/**
	 * Leave-status cell: status text + cancel button when still cancellable.
	 */
	private function statusHtml(HRLeave $leav): string
	{
		$dt = Carbon::now()->lte(Carbon::parse($leav->date_time_start));
		$html = '';

		if (is_null($leav->leave_status_id)) {
			$html .= 'Pending';
			if ($dt) {
				$html .= $this->cancelButton($leav->id);
			}
		} else {
			$html .= e((string) $leav->belongstooptleavestatus?->status);
			if ($dt && ($leav->leave_status_id == 5 || $leav->leave_status_id == 6)) {
				$html .= $this->cancelButton($leav->id);
			}
		}

		return $html;
	}

	private function cancelButton(int $id): string
	{
		return ' <a href="#" class="btn btn-sm btn-outline-secondary cancel_btn" id="cancel_btn_'.$id.'" data-id="'.$id.'" alt="Cancel" title="Cancel"><i class="fas fa-ban"></i></a>';
	}
}
