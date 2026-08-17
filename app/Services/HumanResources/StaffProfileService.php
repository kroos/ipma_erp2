<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\Login;
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptLeaveType;
use App\Models\HumanResources\OptWorkingHour;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
		foreach ($childrens as $sc) {
			// age was computed inline in the blade — moved here
			$sc->age = $sc->dob ? Carbon::parse($sc->dob)->toPeriod(now(), 1, 'year')->count() : null;
		}
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
			$leaves = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
				->where('staff_id', $person->id)
				->whereYear('date_time_start', $al->year)
				->whereIn('leave_type_id', [1, 5])
				->get();
			$annualMap[$al->year] = $leaves;
			// decorate (was inline blade lookup + accumulator)
			$al->leaves = $leaves;
			$al->total_days = $leaves->sum('period_day');
			foreach ($leaves as $lv) {
				$lv->leave_ref = $this->leaveRef($lv);
			}
		}

		$mcMap = [];
		foreach ($mcLeaves as $al) {
			$leaves = HRLeave::where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})
				->where('staff_id', $person->id)
				->whereYear('date_time_start', $al->year)
				->where('leave_type_id', 2)
				->get();
			$mcMap[$al->year] = $leaves;
			$al->leaves = $leaves;
			$al->total_days = $leaves->sum('period_day');
			foreach ($leaves as $lv) {
				$lv->leave_ref = $this->leaveRef($lv);
			}
		}

		// maternity has no year filter in the original view — single shared list
		$maternity = HRLeave::where(function (Builder $query) {
			$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
		})		->where('staff_id', $person->id)
		->where('leave_type_id', 7)
		->get();
		foreach ($maternity as $lv) {
			$lv->leave_ref = $this->leaveRef($lv);
		}
		foreach ($maternityLeaves as $al) {
			$al->leaves = $maternity;
			$al->total_days = $maternity->sum('period_day');
		}

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
			$leaves = $al->belongstomanyleave()->where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
			})->get();
			$replacementMap[$al->id] = $leaves;
			$al->leaves = $leaves;
			$al->total_days = $leaves->sum('period_day');
			foreach ($leaves as $lv) {
				$lv->leave_ref = $this->leaveRef($lv);
			}
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
		})		->where('staff_id', $person->id)
		->whereIn('leave_type_id', [3, 6, 12])
		->get();
		foreach ($leavesupls as $lv) {
			$lv->leave_ref = $this->leaveRef($lv);
			$lv->from_fmt = $lv->date_time_start ? Carbon::parse($lv->date_time_start)->format('j M Y') : '';
			$lv->to_fmt = $lv->date_time_end ? Carbon::parse($lv->date_time_end)->format('j M Y') : '';
		}

		$leavesmcs = HRLeave::where(function (Builder $query) {
			$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
		})
		->where('staff_id', $person->id)
		->where('leave_type_id', 11)
		->get();
		foreach ($leavesmcs as $lv) {
			$lv->leave_ref = $this->leaveRef($lv);
			$lv->from_fmt = $lv->date_time_start ? Carbon::parse($lv->date_time_start)->format('j M Y') : '';
			$lv->to_fmt = $lv->date_time_end ? Carbon::parse($lv->date_time_end)->format('j M Y') : '';
		}

		return compact('leavesupls', 'leavesmcs')
			+ ['upl_total' => $leavesupls->sum('period_day'), 'mcupl_total' => $leavesmcs->sum('period_day')];
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

  /**
   * True when the current user is a system admin (staff index "Staff ID"
   * column visibility).
   */
  public function isAdmin(): bool
  {
    return auth()->user()->belongstostaff->authorise_id == 1;
  }

  /**
   * Formatted identity-card dates (were inline Carbon::parse calls in the
   * show blade).
   */
  public function profileCard(Staff $person): array
  {
    return [
      'dob_fmt' => $person->dob ? Carbon::parse($person->dob)->format('j M Y') : null,
      'join_fmt' => $person->join ? Carbon::parse($person->join)->format('j M Y') : null,
      'confirmed_fmt' => $person->confirmed ? Carbon::parse($person->confirmed)->format('j M Y') : null,
    ];
  }

  /**
   * HR9-xxxxx/yyyy reference for a leave (was str_pad inline in the blades).
   */
  private function leaveRef(HRLeave $leave): string
  {
    return 'HR9-' . str_pad($leave->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $leave->leave_year;
  }

  /**
   * Staff index list — active/inactive display rows, access-filtered for the
   * current user. The whole permission tree that lived inside the index blade
   * moved here, with O(1) extra queries (precomputed dept map + login map,
   * eager-loaded relations) instead of N+1 per row.
   */
  public function indexData(): array
  {
    $user = auth()->user()->belongstostaff;
    $me1 = $user->div_id == 1;        // hod
    $me2 = $user->div_id == 5;        // hod assistant
    $me3 = $user->div_id == 4;        // supervisor
    $me5 = $user->authorise_id == 1;  // admin
    $me6 = $user->div_id == 2;        // director
    $dept = $user->belongstomanydepartment()->wherePivot('main', 1)->first();
    $deptid = $dept?->id;
    $branch = $dept?->branch_id;

    $with = [
      'belongstorestdaygroup',
      'belongstonationality',
      'belongstomaritalstatus',
      'belongstoleaveapprovalflow',
      'belongstomanydepartment' => fn ($q) => $q->wherePivot('main', 1)->with(['belongstocategory', 'belongstobranch']),
    ];

    $active = Staff::with($with)->where('active', 1)->orderBy('id')->get();
    $inactive = Staff::with($with)->where('active', '<>', 1)->orderBy('id')->get();

    $ids = $active->pluck('id')->merge($inactive->pluck('id'))->unique()->values();

    // staff -> main dept (id, category_id, branch_id) — one query
    $deptMap = collect();
    if ($ids->isNotEmpty()) {
      $deptMap = DB::table('pivot_staff_pivotdepts as pspd')
        ->join('pivot_dept_cate_branches as pd', 'pd.id', '=', 'pspd.pivot_dept_id')
        ->where('pspd.main', 1)
        ->whereIn('pspd.staff_id', $ids)
        ->whereNull('pd.deleted_at')
        ->get(['pspd.staff_id', 'pd.id', 'pd.category_id', 'pd.branch_id'])
        ->keyBy('staff_id');
    }

    // staff -> active login username — one query
    $usernameMap = collect();
    if ($ids->isNotEmpty()) {
      $usernameMap = Login::where('active', 1)->whereIn('staff_id', $ids)->get()->keyBy('staff_id');
    }

    $visible = function (Staff $s) use ($me1, $me2, $me3, $me5, $me6, $deptid, $branch, $deptMap) {
      $d = $deptMap[$s->id] ?? null;
      $stdept = $d?->id;
      $stcate = $d?->category_id;
      $stbrch = $d?->branch_id;

      if ($me1) {                                                // hod
        if ($deptid == 21 || $deptid == 28) {                    // dept prod A / prod B
          return $stdept == $deptid || $stcate == 2;
        }
        if ($deptid == 14) {                                     // HR
          return true;
        }
        if ($deptid == 6) {                                      // cust serv
          return $stdept == $deptid || $stdept == 7;
        }
        if ($deptid == 23) {                                     // purchasing
          return $stdept == $deptid || $stdept == 16 || $stdept == 17;
        }
        return $stdept == $deptid;                               // other dept
      }
      if ($me2) {                                                // asst hod
        if ($deptid == 14) {
          return true;
        }
        if ($deptid == 6) {
          return $stdept == $deptid || $stdept == 7;
        }
        return false;
      }
      if ($me3) {                                                // supervisor
        return ($branch == 1 || $branch == 2) && ($stdept == $deptid || ($stcate == 2 && $stbrch == $branch));
      }
      if ($me6 || $me5) {                                        // director / admin
        return true;
      }
      return false;
    };

    $row = function (Staff $s) use ($usernameMap) {
      $mainDept = $s->belongstomanydepartment->first();
      return [
        'id' => $s->id,
        'staff_id' => $s->id,
        'username' => $usernameMap[$s->id]?->username ?? '',
        'name' => $s->name ?? '',
        'image' => $s->image ?? '',
        'group' => $s->belongstorestdaygroup?->group ?? '',
        'nationality' => $s->belongstonationality?->country ?? '',
        'marital_status' => $s->belongstomaritalstatus?->marital_status ?? '',
        'category' => $mainDept?->belongstocategory?->category ?? '',
        'department' => $mainDept?->department ?? '',
        'location' => $mainDept?->belongstobranch?->location ?? '',
        'leave_flow' => $s->belongstoleaveapprovalflow?->description ?? '',
        'mobile' => $s->mobile ?? '',
        'show_url' => route('staff.show', $s->id),
      ];
    };

    return [
      'is_admin' => (bool) $me5,
      'active' => $active->filter($visible)->map($row)->values()->all(),
      'inactive' => $inactive->filter($visible)->map($row)->values()->all(),
    ];
  }

  /**
   * Attendance table rows for a year/month (staff show screen). Query + the
   * per-row time/colour/leave decoration moved out of the blade.
   */
  public function attendanceRows(Staff $person, string $year, string $month): array
  {
    $attendance = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
      ->where('hr_attendances.staff_id', $person->id)
      ->whereYear('hr_attendances.attend_date', '=', $year)
      ->whereMonth('hr_attendances.attend_date', '=', $month)
      ->select('hr_attendances.remarks as attend_remark', 'hr_attendances.*', 'staffs.*')
      ->get();

    $whGroup = (string) ($person->belongstomanydepartment()->wherePivot('main', 1)->first()?->wh_group_id ?? 0);
    $decor = $this->attendanceDecor($attendance, $whGroup);

    $rows = [];
    foreach ($attendance as $attend) {
      $company_hour = $decor['companyHours'][$attend->id] ?? null;
      $daytype = $decor['daytypes'][$attend->id] ?? null;
      $outstation = $decor['outstations'][$attend->id] ?? null;
      $overtime = $decor['overtimes'][$attend->id] ?? null;
      $leaveInfo = $decor['leaveInfos'][$attend->id] ?? null;

      $in = ($attend->in != null && $attend->in != '00:00:00') ? Carbon::parse($attend->in)->format('h:i a') : null;
      $color_in = ($company_hour && $in && $attend->in > $company_hour->time_start_am) ? 'color:red' : '';

      $break = ($attend->break != null && $attend->break != '00:00:00') ? Carbon::parse($attend->break)->format('h:i a') : null;
      $color_break = ($company_hour && $break && $attend->break < $company_hour->time_end_am) ? 'color:red' : '';

      $resume = ($attend->resume != null && $attend->resume != '00:00:00') ? Carbon::parse($attend->resume)->format('h:i a') : null;
      $color_resume = ($company_hour && $resume && $attend->resume > $company_hour->time_start_pm) ? 'color:red' : '';

      $out = ($attend->out != null && $attend->out != '00:00:00') ? Carbon::parse($attend->out)->format('h:i a') : null;
      $color_out = ($company_hour && $out && $attend->out < $company_hour->time_end_pm) ? 'color:red' : '';

      $work_hour = ($attend->time_work_hour != null && $attend->time_work_hour != '00:00:00')
        ? Carbon::parse($attend->time_work_hour)->format('H:i')
        : null;

      $rows[] = [
        'date' => $attend->attend_date ? Carbon::parse($attend->attend_date)->format('j M Y') : '',
        'daytype' => $daytype?->daytype ?? '',
        'in' => $in !== null ? '<span style="' . $color_in . '">' . $in . '</span>' : '',
        'break' => $break !== null ? '<span style="' . $color_break . '">' . $break . '</span>' : '',
        'resume' => $resume !== null ? '<span style="' . $color_resume . '">' . $resume . '</span>' : '',
        'out' => $out !== null ? '<span style="' . $color_out . '">' . $out . '</span>' : '',
        'work_hour' => $work_hour ?? '',
        'overtime' => $overtime
          ? '<span data-bs-toggle="tooltip" data-bs-html="true" title="' . e($overtime) . '">' . e($overtime) . '</span>'
          : '',
        'leave_form' => $leaveInfo
          ? '<a href="' . route('leave.show', $leaveInfo['id']) . '" target="_blank">' . e($leaveInfo['form']) . '</a>'
          : '',
        'leave_type' => $leaveInfo['type'] ?? '',
        'remark' => $attend->attend_remark
          ? '<span class="text-truncate" data-bs-toggle="tooltip" title="' . e($attend->attend_remark) . '">' . Str::limit($attend->attend_remark, 7, ' >>') . '</span>'
          : '',
        'outstation' => $outstation
          ? '<span class="text-truncate" data-bs-toggle="tooltip" title="' . e($outstation) . '">' . Str::limit($outstation, 7, ' >>') . '</span>'
          : '',
      ];
    }
    return $rows;
  }

  /**
   * Leave table rows (staff show screen) — the leave-record decoration that
   * used to live in the blade loop.
   */
  public function leaveRows(Staff $person): array
  {
    $leave_records = HRLeave::where('staff_id', $person->id)
      ->orderBy('date_time_start', 'DESC')
      ->orderBy('leave_type_id', 'ASC')
      ->orderBy('leave_status_id', 'DESC')
      ->get();

    $rows = [];
    foreach ($leave_records as $ls) {
      if ($ls->leave_type_id == 9 || ($ls->leave_type_id != 9 && ($ls->half_type_id == 2 || $ls->half_type_id == 1))) {
        $dts = Carbon::parse($ls->date_time_start)->format('j M Y g:i a');
        $dte = Carbon::parse($ls->date_time_end)->format('j M Y g:i a');
        if ($ls->leave_type_id == 9) {
          $i = Carbon::parse($ls->period_time);
          $dper = $i->hour . ' hour, ' . $i->minute . ' minutes';
        } else {
          $dper = $ls->period_day . ' Day';
        }
      } else {
        $dts = Carbon::parse($ls->date_time_start)->format('j M Y ');
        $dte = Carbon::parse($ls->date_time_end)->format('j M Y ');
        $dper = $ls->period_day . ' day/s';
      }

      $rows[] = [
        'no' => $this->leaveRef($ls),
        'type' => $ls->belongstooptleavetype?->leave_type_code ?? '',
        'applied' => Carbon::parse($ls->created_at)->format('j M Y g:i a'),
        'from' => $dts,
        'to' => $dte,
        'duration' => $dper,
        'reason' => $ls->reason
          ? '<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="' . e($ls->reason) . '">' . Str::limit($ls->reason, 10, '>') . '</span>'
          : '',
        'status' => is_null($ls->leave_status_id) ? 'Pending' : ($ls->belongstooptleavestatus?->status ?? ''),
        'show_url' => route('hrleave.show', $ls->id),
      ];
    }
    return $rows;
  }

  /**
   * Replacement-leave table rows (staff show screen).
   */
  public function replacementRows(Staff $person): array
  {
    $replacementLeaves = $person->hasmanyleavereplacement()->orderBy('date_start', 'DESC')->get();

    $rows = [];
    foreach ($replacementLeaves as $al) {
      $leaves = $al->belongstomanyleave()->where(function (Builder $query) {
        $query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
      })->get();

      $rows[] = [
        'from' => Carbon::parse($al->date_start)->format('j M Y'),
        'to' => Carbon::parse($al->date_end)->format('j M Y'),
        'location' => $al->belongstocustomer?->customer ?? '',
        'reason' => $al->reason ?? '',
        'leave_total' => $al->leave_total,
        'leave_utilize' => $al->leave_utilize,
        'leave_balance' => $al->leave_balance,
        'leaves' => $leaves->map(fn ($l) => [
          'no' => $this->leaveRef($l),
          'period_day' => $l->period_day,
          'show_url' => route('hrleave.show', $l->id),
        ])->values()->all(),
        'total_days' => $leaves->sum('period_day'),
        'edit_url' => route('rleave.edit', $al->id),
      ];
    }
    return $rows;
  }

  /**
   * Disciplinary table rows (staff show screen).
   */
  public function disciplineRows(Staff $person): array
  {
    $disciplinaries = $person->hasmanyhrdisciplinary()->orderBy('misconduct_date', 'DESC')->get();

    $rows = [];
    foreach ($disciplinaries as $al) {
      $rows[] = [
        'id' => $al->id,
        'action' => $al->belongstooptdisciplinaryaction?->disciplinary_action ?? '',
        'violation' => $al->belongstooptviolation?->violation ?? '',
        'reason' => $al->reason
          ? '<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="' . e($al->reason) . '">' . Str::limit($al->reason, 10, '>') . '</span>'
          : '',
        'date' => Carbon::parse($al->misconduct_date)->format('j M Y'),
        'softcopy_url' => $al->softcopy ? asset('storage/disciplinary/' . $al->softcopy) : '',
        'softcopy' => $al->softcopy ?? '',
        'edit_url' => route('discipline.edit', $al->id),
      ];
    }
    return $rows;
  }
}
