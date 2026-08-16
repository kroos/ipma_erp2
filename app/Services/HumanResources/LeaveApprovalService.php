<?php

namespace App\Services\HumanResources;

// load models (only those referenced statically in the spliced row blocks)
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptLeaveStatus;
use App\Models\HumanResources\HRAttendance;

// load sql builder
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// load models (for the hrdept leave list decoration)
use App\Models\Login;

// load Carbon
use Carbon\Carbon;

/**
 * M2 W5 — leave-approval shared service.
 * Holds the auth/dept context and per-row grid computation that used to live
 * inside the four approval blades (hod / supervisor / dir / hr).
 */
class LeaveApprovalService
{
    /**
     * Shared page context: who am I + approval-flow flags + status options.
     * Returned as an array for view('...', $context).
     */
    public function context(): array
    {
        $user = auth()->user()->belongstostaff;
        $auth = $user->div_id; // 1/2/5
        $me1 = $user->div_id == 1;    // hod
        $me2 = $user->div_id == 5;    // hod assistant
        $me3 = $user->div_id == 4;    // supervisor
        $me4 = $user->div_id == 3;    // HR
        $me5 = $user->authorise_id == 1;  // admin
        $me6 = $user->div_id == 2;    // director
        $dept = $user->belongstomanydepartment()->wherePivot('main', 1)->first();
        $deptid = $dept?->id;
        $branch = $dept?->branch_id;
        $category = $dept?->category_id;

        $s1 = $me3 || (($me1 || $me2) && $dept?->department_id == 14) || $me5;  // supervisor and hod HR
        $h1 = $me1 || (($me1 || $me2) && $dept?->department_id == 14) || $me5;  // HOD and hod HR
        $d1 = $me6 || ($me1 && $dept?->department_id == 14) || $me5;  // dir and hod HR
        $r1 = (($me1 || $me2) && $dept?->department_id == 14) || $me5;  // hod HR

        // first (non-main) dept id used by the verify-code inputs in the blades
        $firstdeptid = $user->belongstomanydepartment->first()?->id;

        // supervisor branch filter (used by supervisorleaveapproval body)
        $us = $user->belongstomanydepartment->first()?->branch_id;

        // for supervisor and hod approval
        if ($me6) {                                      // only director
            $c = OptLeaveStatus::whereIn('id', [4, 5, 6])->get();                // only rejected, approve and waived
        } else {
            $c = OptLeaveStatus::whereIn('id', [4, 5])->get();                // only rejected and approve
        }

        $ls = [];
        foreach ($c as $v) {
            $ls[] = ['id' => $v->id, 'text' => $v->status];
        }

        return compact(
            'user', 'auth', 'me1', 'me2', 'me3', 'me4', 'me5', 'me6',
            'dept', 'deptid', 'branch', 'category', 'firstdeptid', 'us',
            's1', 'h1', 'd1', 'r1', 'ls'
        );
    }

    /**
     * hrdept/leave/{index,cancel,reject} shared row logic.
     * Moves the per-row permission tree (Staff::find + main-dept lookup) and
     * the username lookup out of the blades into a single pass so the list is
     * built with O(1) extra queries instead of N+1 per row.
     *
     * @param  Collection  $leaves  raw HRLeave collection (already date/status filtered)
     * @param  string  $variant  one of: 'up' (upleave / cancel / reject), 'to' (toleave), 'pa' (paleave)
     * @return Collection  rows the current user is allowed to see, each with a `username` attribute
     */
    public function departmentLeaves(Collection $leaves, string $variant): Collection
    {
        $ctx = $this->context();
        $me1 = $ctx['me1'];
        $me2 = $ctx['me2'];
        $me3 = $ctx['me3'];
        $me5 = $ctx['me5'];
        $me6 = $ctx['me6'];
        $deptid = $ctx['deptid'];
        $branch = $ctx['branch'];

        $staffIds = $leaves->pluck('staff_id')->filter()->unique()->values();

        // main department of each staff: staff_id => {id, category_id, branch_id}
        $deptMap = [];
        if ($staffIds->isNotEmpty()) {
            $deptMap = DB::table('pivot_staff_pivotdepts as pspd')
                ->join('pivot_dept_cate_branches as pd', 'pd.id', '=', 'pspd.pivot_dept_id')
                ->where('pspd.main', 1)
                ->whereIn('pspd.staff_id', $staffIds)
                ->whereNull('pd.deleted_at')
                ->get(['pspd.staff_id', 'pd.id', 'pd.category_id', 'pd.branch_id'])
                ->keyBy('staff_id')
                ->all();
        }

        // active login username of each staff
        $usernameMap = collect();
        if ($staffIds->isNotEmpty()) {
            $usernameMap = Login::where('active', 1)->whereIn('staff_id', $staffIds)->get()->keyBy('staff_id');
        }

        return $leaves->filter(function ($leave) use ($variant, $me1, $me2, $me3, $me5, $me6, $deptid, $branch, $deptMap, $usernameMap) {
            $d = $deptMap[$leave->staff_id] ?? null;
            $dept = $d?->id;
            $cate = $d?->category_id;
            $brch = $d?->branch_id;

            $ha = false;
            if ($me1) {                                                                                 // hod
                if ($deptid == 21 || $deptid == 28) {                                                   // hod | dept prod A | dept prod B
                    $ha = $dept == $deptid || $cate == 2;
                } elseif ($deptid == 14) {                                                              // hod | not dept prod A | not dept prod B | HR
                    $ha = true;
                } elseif ($deptid == 6) {                                                               // hod | not dept prod A | not dept prod B | not HR | cust serv
                    $ha = $dept == $deptid || $dept == 7 || $dept == 3;
                } elseif ($deptid == 23) {                                                              // hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
                    $ha = $dept == $deptid || $dept == 16 || ($variant === 'pa' && $dept == 11) || $dept == 17;
                } else {                                                                                // hod | other dept
                    $ha = $dept == $deptid;
                }
            } elseif ($me2) {                                                                           // not hod | asst hod
                if ($deptid == 14) {                                                                    // not hod | not dept prod A | not dept prod B | HR
                    $ha = true;
                } elseif ($deptid == 6) {                                                               // not hod | not dept prod A | not dept prod B | not HR | cust serv
                    $ha = $dept == $deptid || $dept == 7 || ($variant !== 'to' && $dept == 3);
                }
            } elseif ($me3) {                                                                           // not hod | not asst hod | supervisor
                if ($branch == 1 || $branch == 2) {                                                     // supervisor | branch A or branch B
                    $ha = $dept == $deptid || ($cate == 2 && $brch == $branch);
                }
            } elseif ($me6) {                                                                           // not hod | not asst hod | not supervisor | director
                $ha = true;
            } elseif ($me5) {                                                                           // not hod | not asst hod | not supervisor | not director | admin
                $ha = true;
            }

            if (!$ha) {
                return false;
            }

            $leave->username = $usernameMap[$leave->staff_id]?->username ?? null;
            return true;
        })->values();
    }

    /**
     * Pending approvals for a given approval type.
     */
    public function approvals(string $model): Collection
    {
        return $model::whereNull('leave_status_id')->get();
    }

    /**
     * Dispatch to the per-type grid builder.
     */
    public function gridData(Collection $approvals, string $type): array
    {
        return match ($type) {
            'hod' => $this->hodGridData($approvals),
            'supervisor' => $this->supervisorGridData($approvals),
            'dir' => $this->dirGridData($approvals),
            'hr' => $this->hrGridData($approvals),
            default => throw new \InvalidArgumentException("Unknown approval type: {$type}"),
        };
    }
    /**
     * hod approval grid — per-row display data keyed by approval id.
     * Row logic spliced verbatim from the original blade (M2 W5).
     */
    public function hodGridData(Collection $approvals): array
    {
        $grid = [];
        $i = 1; // original blade pre-loop init (leave_type 9 branch re-assigns it)
        foreach ($approvals as $a) {
        $count = 0;
        $supervisor_no = 0;
        $hod_no = 0;
        $director_no = 0;
        $leav = HRLeave::find($a->leave_id);
        $staff = Staff::find($leav->staff_id);
        // dump($staff);
        $sta = $staff->belongstomanydepartment()->wherePivot('main', 1)->first();
        $stadept = $sta->id;
        $stacate = $sta->category_id;
        // dd($stadept, $stacate);

        if (($leav->leave_type_id == 9) || ($leav->leave_type_id != 9 && $leav->half_type_id == 2) || ($leav->leave_type_id != 9 && $leav->half_type_id == 1)) {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y g:i a');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y g:i a');

          if ($leav->leave_type_id != 9) {
            if ($leav->half_type_id == 2) {
              $dper = $leav->period_day . ' Day';
            } elseif ($leav->half_type_id == 1) {
              $dper = $leav->period_day . ' Day';
            }
          } elseif ($leav->leave_type_id == 9) {
            $i = \Carbon\Carbon::parse($leav->period_time);
            $dper = $i->hour . ' hour, ' . $i->minute . ' minutes';
          }
        } else {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y ');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y ');
          $dper = $leav->period_day . ' day/s';
        }

        $z = \Carbon\Carbon::parse(now())->daysUntil($leav->date_time_start, 1)->count();

        if (3 >= $z && $z >= 2) {
          $u = 'table-warning';
        } elseif ($z < 2) {
          $u = 'table-danger';
        } else {
          $u = NULL;
        }

        // find leave backup if any
        $backup = $leav->hasmanyleaveapprovalbackup()?->get();

        if ($backup->count()) {
          if (is_null($backup->first()->leave_status_id)) {
            $bapp = '<span class="text-warning" style="background-color:transparent;">Pending</span>';
            $bappb = false;
            $backup_person = "box-red"; // INDICATOR
          } else {
            $bapp = '<span class="text-success" style="background-color:transparent;">' . e(OptLeaveStatus::find($backup->first()->leave_status_id)->status) . '</span>';
            $bappb = true;
            $backup_person = "box-green"; // INDICATOR
          }
        } else {
          $bapp = '<span class="text-danger" style="background-color:transparent;">No Backup</span>';
          $bappb = true;
          $backup_person = "box-red";
        }

        $hrremarksattendance = HRAttendance::where(function (Builder $query) use ($leav) {
          $query->whereDate('attend_date', '>=', $leav->date_time_start)
            ->whereDate('attend_date', '<=', $leav->date_time_end);
        })
          ->where('staff_id', $leav->staff_id)
          ->where(function (Builder $query) {
            $query->whereNotNull('remarks')->orWhereNotNull('hr_remarks');
          })
          // ->ddrawsql();
          ->get();

        $supervisor = $leav->hasmanyleaveapprovalsupervisor?->first();
        $hod = $leav->hasmanyleaveapprovalhod?->first();
        $director = $leav->hasmanyleaveapprovaldir?->first();
        $hr = $leav->hasmanyleaveapprovalhr?->first();

        // entitlement
        $annl = $staff->hasmanyleaveannual()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $mcel = $staff->hasmanyleavemc()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $matl = $staff->hasmanyleavematernity()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $replt = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_total) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $replb = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_balance) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $upal = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->whereIn('leave_type_id', [3, 6])
          ->get();
        $mcupl = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->where('leave_type_id', 11)
          ->get();

        // INDICATOR
        $leave_type_code = $leav->belongstooptleavetype?->leave_type_code;

        if (strpos($leave_type_code, 'EL') === false) {
          $sop = 'box-green';
        } else {
          $sop = 'box-red';
        }

        if (strpos($leave_type_code, 'UPL') === false) {
          $leave_type = 'box-green';
        } else {
          $leave_type = 'box-red';
        }

        if ($leave_type_code == 'AL' || $leave_type_code == 'NRL' || $leave_type_code == 'ML') {
          $support_doc = 'box-green';
        } else {
          if ($leav->softcopy != NUll) {
            $support_doc = 'box-green';
          } else {
            $support_doc = 'box-red';
          }
        }
        // -------------------------- CALCULATE ATTENDANCE PERCENTAGE --------------------------
        $st = Staff::find($leav->staff_id);
        $soy = now()->copy()->startOfYear();        // early this year
        $lsoy = $soy->copy()->subYear();          // early last year
        // dd($lsoy);
        // dd($lsoy->diffInMonths(now()));

        for ($no = 0; $no <= $soy->diffInMonths(now()); $no++) { // take only 2 years back
          $sm = $soy->copy()->addMonth($no);
          $em = $sm->copy()->endOfMonth();
          // dump([$sm, $em]);

          $sq = $st->hasmanyattendance()
            ->whereDate('attend_date', '>=', $sm)
            ->whereDate('attend_date', '<=', $em)
            ->where('daytype_id', 1)
            ->get();
          // ->ddRawSql();

          $fdl = 0;
          $aaa = 0;
          if ($sq->count()) {
            $workday = $sq->count();                            // working days
            // dump([$workday, $sm->format('M Y')]);

            foreach ($sq as $s) {
              $fulldayleave = $s->belongstoleave()?->where(function (Builder $query) {
                // $fulldayleave = HRLeave::where(function (Builder $query){
                $query->where('leave_type_id', '<>', 9)
                  ->where(function (Builder $query) {
                    $query->where('half_type_id', '<>', 2)
                      ->orWhereNull('half_type_id');
                  });
              })
                ->where(function (Builder $query) {
                  $query->whereIn('leave_status_id', [5, 6])
                    ->orWhereNull('leave_status_id');
                })
                ->where(function (Builder $query) use ($s) {
                  $query->whereDate('date_time_start', '<=', $s->attend_date)
                    ->WhereDate('date_time_end', '>=', $s->attend_date);
                })
                ->get();
              $fdl += $fulldayleave->count();
              // dump($fulldayleave->count().' fulldayleave count');

              $absent = $s->where('attendance_type_id', 1)
                // $absent = HRAttendance::where('attendance_type_id', 1)
                ->whereDate('attend_date', $s->attend_date)
                ->where('daytype_id', 1)
                ->where('staff_id', $st->id)
                ->get();
              $aaa += $absent->count();
              // dump($absent.' absent');
            }
            $percentage = (($workday - $fdl - $aaa) / $workday) * 100;
          } else {
            $workday = 0;
            // $fdl = 0;
            $percentage = 0;
          }

          //   'month' => $sm->format('M Y'),
          //   'percentage' => $percentage,
          //   'workdays' => $workday,
          //   'leaves' => $fdl,
          //   'absents' => $aaa,
          //   'working_days' => ($workday - $fdl - $aaa),
        }

        if ($percentage >= 80) {
          $attendance_percentage = 'box-green';
        } else {
          $attendance_percentage = 'box-red';
        }

        // M2 W5: precompute body-level lookups (were inline blade queries)
        $leavtype = $leav->belongstooptleavetype;
        $username = $staff?->hasmanylogin()?->where('active', 1)->first()?->username;
        $amend = $leav->hasmanyleaveamend()->get();

            $row = get_defined_vars();
            unset($row['approvals'], $row['grid'], $row['row'], $row['a']);
            $grid[$a->id] = $row;
        }
        return $grid;
    }
    /**
     * supervisor approval grid — per-row display data keyed by approval id.
     * Row logic spliced verbatim from the original blade (M2 W5).
     */
    public function supervisorGridData(Collection $approvals): array
    {
        $grid = [];
        $i = 1; // original blade pre-loop init (leave_type 9 branch re-assigns it)
        foreach ($approvals as $a) {
        $count = 0;
        $supervisor_no = 0;
        $hod_no = 0;
        $director_no = 0;
        $hr_no = 0;
        $leav = HRLeave::find($a->leave_id);
        $staff1 = Staff::find($leav->staff_id);
        $ul = $leav?->belongstostaff?->belongstomanydepartment->first()?->branch_id;        //get user leave branch_id
        $udept = $leav?->belongstostaff?->belongstomanydepartment->first()?->id;

        if (($leav->leave_type_id == 9) || ($leav->leave_type_id != 9 && $leav->half_type_id == 2) || ($leav->leave_type_id != 9 && $leav->half_type_id == 1)) {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y g:i a');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y g:i a');

          if ($leav->leave_type_id != 9) {
            if ($leav->half_type_id == 2) {
              $dper = $leav->period_day . ' Day';
            } elseif ($leav->half_type_id == 1) {
              $dper = $leav->period_day . ' Day';
            }
          } elseif ($leav->leave_type_id == 9) {
            $i = \Carbon\Carbon::parse($leav->period_time);
            $dper = $i->hour . ' hour, ' . $i->minute . ' minutes';
          }
        } else {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y ');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y ');
          $dper = $leav->period_day . ' day/s';
        }

        $z = \Carbon\Carbon::parse(now())->daysUntil($leav->date_time_start, 1)->count();

        if (3 >= $z && $z >= 2) {
          $u = 'table-warning';
        } elseif ($z < 2) {
          $u = 'table-danger';
        } else {
          $u = NULL;
        }

        // find leave backup if any
        $backup = $leav->hasmanyleaveapprovalbackup()->get();

        if ($backup->count()) {
          if (is_null($backup->first()->leave_status_id)) {
            $bapp = '<span class="text-warning" style="background-color:transparent;">Pending</span>';
            $bappb = false;
            $backup_person = "box-red"; // INDICATOR
          } else {
            $bapp = '<span class="text-success" style="background-color:transparent;">' . e(OptLeaveStatus::find($backup->first()->leave_status_id)->status) . '</span>';
            $bappb = true;
            $backup_person = "box-green"; // INDICATOR
          }
        } else {
          $bapp = '<span class="text-danger" style="background-color:transparent;">No Backup</span>';
          $bappb = true;
          $backup_person = "box-red";
        }

        $hrremarksattendance = HRAttendance::where(function (Builder $query) use ($leav) {
          $query->whereDate('attend_date', '>=', $leav->date_time_start)
            ->whereDate('attend_date', '<=', $leav->date_time_end);
        })
          ->where('staff_id', $leav->staff_id)
          ->where(function (Builder $query) {
            $query->whereNotNull('remarks')->orWhereNotNull('hr_remarks');
          })
          ->get();

        $supervisor = $leav->hasmanyleaveapprovalsupervisor?->first();
        $hod = $leav->hasmanyleaveapprovalhod?->first();
        $director = $leav->hasmanyleaveapprovaldir?->first();
        $hr = $leav->hasmanyleaveapprovalhr?->first();

        // entitlement
        $annl = $staff1->hasmanyleaveannual()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $mcel = $staff1->hasmanyleavemc()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $matl = $staff1->hasmanyleavematernity()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $replt = $staff1->hasmanyleavereplacement()?->selectRaw('SUM(leave_total) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $replb = $staff1->hasmanyleavereplacement()?->selectRaw('SUM(leave_balance) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $upal = $staff1->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->whereIn('leave_type_id', [3, 6])
          ->get();
        $mcupl = $staff1->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->where('leave_type_id', 11)
          ->get();
        $upal = $staff1->hasmanyleave()?->selectRaw('SUM(period_day) as total')
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
        $mcupl = $staff1->hasmanyleave()?->selectRaw('SUM(period_day) as total')
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

        // INDICATOR
        $leave_type_code = $leav->belongstooptleavetype?->leave_type_code;

        if (strpos($leave_type_code, 'EL') === false) {
          $sop = 'box-green';
        } else {
          $sop = 'box-red';
        }

        if (strpos($leave_type_code, 'UPL') === false) {
          $leave_type = 'box-green';
        } else {
          $leave_type = 'box-red';
        }

        if ($leave_type_code == 'AL' || $leave_type_code == 'NRL' || $leave_type_code == 'ML') {
          $support_doc = 'box-green';
        } else {
          if ($leav->softcopy != NUll) {
            $support_doc = 'box-green';
          } else {
            $support_doc = 'box-red';
          }
        }
        // -------------------------- CALCULATE ATTENDANCE PERCENTAGE --------------------------
        $st = Staff::find($leav->staff_id);
        $soy = now()->copy()->startOfYear();        // early this year
        $lsoy = $soy->copy()->subYear();          // early last year
        // dd($lsoy);
        // dd($lsoy->diffInMonths(now()));

        for ($no = 0; $no <= $soy->diffInMonths(now()); $no++) { // take only 2 years back
          $sm = $soy->copy()->addMonth($no);
          $em = $sm->copy()->endOfMonth();
          // dump([$sm, $em]);

          $sq = $st->hasmanyattendance()
            ->whereDate('attend_date', '>=', $sm)
            ->whereDate('attend_date', '<=', $em)
            ->where('daytype_id', 1)
            ->get();
          // ->ddRawSql();

          $fdl = 0;
          $aaa = 0;
          if ($sq->count()) {
            $workday = $sq->count();                            // working days
            // dump([$workday, $sm->format('M Y')]);

            foreach ($sq as $s) {
              $fulldayleave = $s->belongstoleave()?->where(function (Builder $query) {
                // $fulldayleave = HRLeave::where(function (Builder $query){
                $query->where('leave_type_id', '<>', 9)
                  ->where(function (Builder $query) {
                    $query->where('half_type_id', '<>', 2)
                      ->orWhereNull('half_type_id');
                  });
              })
                ->where(function (Builder $query) {
                  $query->whereIn('leave_status_id', [5, 6])
                    ->orWhereNull('leave_status_id');
                })
                ->where(function (Builder $query) use ($s) {
                  $query->whereDate('date_time_start', '<=', $s->attend_date)
                    ->WhereDate('date_time_end', '>=', $s->attend_date);
                })
                ->get();
              $fdl += $fulldayleave->count();
              // dump($fulldayleave->count().' fulldayleave count');

              $absent = $s->where('attendance_type_id', 1)
                // $absent = HRAttendance::where('attendance_type_id', 1)
                ->whereDate('attend_date', $s->attend_date)
                ->where('daytype_id', 1)
                ->where('staff_id', $st->id)
                ->get();
              $aaa += $absent->count();
              // dump($absent.' absent');
            }
            $percentage = (($workday - $fdl - $aaa) / $workday) * 100;
          } else {
            $workday = 0;
            // $fdl = 0;
            $percentage = 0;
          }

          //   'month' => $sm->format('M Y'),
          //   'percentage' => $percentage,
          //   'workdays' => $workday,
          //   'leaves' => $fdl,
          //   'absents' => $aaa,
          //   'working_days' => ($workday - $fdl - $aaa),
        }

        if ($percentage >= 80) {
          $attendance_percentage = 'box-green';
        } else {
          $attendance_percentage = 'box-red';
        }

        // M2 W5: precompute body-level lookups (were inline blade queries)
        $leavtype = $leav->belongstooptleavetype;
        $username = $staff1?->hasmanylogin()?->where('active', 1)->first()?->username;
        $amend = $leav->hasmanyleaveamend()->get();

            $row = get_defined_vars();
            unset($row['approvals'], $row['grid'], $row['row'], $row['a']);
            $grid[$a->id] = $row;
        }
        return $grid;
    }
    /**
     * dir approval grid — per-row display data keyed by approval id.
     * Row logic spliced verbatim from the original blade (M2 W5).
     */
    public function dirGridData(Collection $approvals): array
    {
        $grid = [];
        $i = 1; // original blade pre-loop init (leave_type 9 branch re-assigns it)
        foreach ($approvals as $a) {
        $count = 0;
        $supervisor_no = 0;
        $hod_no = 0;
        $director_no = 0;
        $leav = HRLeave::find($a->leave_id);
        $staff = Staff::find($leav->staff_id);

        if (($leav->leave_type_id == 9) || ($leav->leave_type_id != 9 && $leav->half_type_id == 2) || ($leav->leave_type_id != 9 && $leav->half_type_id == 1)) {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y g:i a');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y g:i a');

          if ($leav->leave_type_id != 9) {
            if ($leav->half_type_id == 2) {
              $dper = $leav->period_day . ' Day';
            } elseif ($leav->half_type_id == 1) {
              $dper = $leav->period_day . ' Day';
            }
          } elseif ($leav->leave_type_id == 9) {
            $i = \Carbon\Carbon::parse($leav->period_time);
            $dper = $i->hour . ' hour, ' . $i->minute . ' minutes';
          }
        } else {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y ');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y ');
          $dper = $leav->period_day . ' day/s';
        }

        $z = \Carbon\Carbon::parse(now())->daysUntil($leav->date_time_start, 1)->count();

        if (3 >= $z && $z >= 2) {
          $u = 'table-warning';
        } elseif ($z < 2) {
          $u = 'table-danger';
        } else {
          $u = NULL;
        }

        // find leave backup if any
        $backup = $leav->hasmanyleaveapprovalbackup()->get();

        if ($backup->count()) {
          if (is_null($backup->first()->leave_status_id)) {
            $bapp = '<span class="text-warning" style="background-color:transparent;">Pending</span>';
            $backup_person = "box-red"; // INDICATOR
          } else {
            $bapp = '<span class="text-success" style="background-color:transparent;">' . e(OptLeaveStatus::find($backup->first()->leave_status_id)->status) . '</span>';
            $backup_person = "box-green"; // INDICATOR
          }
        } else {
          $bapp = '<span class="text-danger" style="background-color:transparent;">No Backup</span>';
          $backup_person = "box-red";
        }

        $hrremarksattendance = HRAttendance::where(function (Builder $query) use ($leav) {
          $query->whereDate('attend_date', '>=', $leav->date_time_start)
            ->whereDate('attend_date', '<=', $leav->date_time_end);
        })
          ->where('staff_id', $leav->staff_id)
          ->where(function (Builder $query) {
            $query->whereNotNull('remarks')->orWhereNotNull('hr_remarks');
          })
          // ->ddrawsql();
          ->get();

        $supervisor = $leav->hasmanyleaveapprovalsupervisor?->first();
        $hod = $leav->hasmanyleaveapprovalhod?->first();
        $director = $leav->hasmanyleaveapprovaldir?->first();
        $hr = $leav->hasmanyleaveapprovalhr?->first();

        // entitlement
        $annl = $staff->hasmanyleaveannual()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $mcel = $staff->hasmanyleavemc()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $matl = $staff->hasmanyleavematernity()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $replt = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_total) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $replb = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_balance) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $upal = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->whereIn('leave_type_id', [3, 6])
          ->get();
        $mcupl = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->where('leave_type_id', 11)
          ->get();

        // INDICATOR
        $leave_type_code = $leav->belongstooptleavetype?->leave_type_code;

        if (strpos($leave_type_code, 'EL') === false) {
          $sop = 'box-green';
        } else {
          $sop = 'box-red';
        }

        if (strpos($leave_type_code, 'UPL') === false) {
          $leave_type = 'box-green';
        } else {
          $leave_type = 'box-red';
        }

        if ($leave_type_code == 'AL' || $leave_type_code == 'NRL' || $leave_type_code == 'ML') {
          $support_doc = 'box-green';
        } else {
          if ($leav->softcopy != NUll) {
            $support_doc = 'box-green';
          } else {
            $support_doc = 'box-red';
          }
        }
        // -------------------------- CALCULATE ATTENDANCE PERCENTAGE --------------------------
        $st = Staff::find($leav->staff_id);
        $soy = now()->copy()->startOfYear();        // early this year
        $lsoy = $soy->copy()->subYear();          // early last year
        // dd($lsoy);
        // dd($lsoy->diffInMonths(now()));

        for ($no = 0; $no <= $soy->diffInMonths(now()); $no++) { // take only 2 years back
          $sm = $soy->copy()->addMonth($no);
          $em = $sm->copy()->endOfMonth();
          // dump([$sm, $em]);

          $sq = $st->hasmanyattendance()
            ->whereDate('attend_date', '>=', $sm)
            ->whereDate('attend_date', '<=', $em)
            ->where('daytype_id', 1)
            ->get();
          // ->ddRawSql();

          $fdl = 0;
          $aaa = 0;
          if ($sq->count()) {
            $workday = $sq->count();                            // working days
            // dump([$workday, $sm->format('M Y')]);

            foreach ($sq as $s) {
              $fulldayleave = $s->belongstoleave()?->where(function (Builder $query) {
                // $fulldayleave = HRLeave::where(function (Builder $query){
                $query->where('leave_type_id', '<>', 9)
                  ->where(function (Builder $query) {
                    $query->where('half_type_id', '<>', 2)
                      ->orWhereNull('half_type_id');
                  });
              })
                ->where(function (Builder $query) {
                  $query->whereIn('leave_status_id', [5, 6])
                    ->orWhereNull('leave_status_id');
                })
                ->where(function (Builder $query) use ($s) {
                  $query->whereDate('date_time_start', '<=', $s->attend_date)
                    ->WhereDate('date_time_end', '>=', $s->attend_date);
                })
                ->get();
              $fdl += $fulldayleave->count();
              // dump($fulldayleave->count().' fulldayleave count');

              $absent = $s->where('attendance_type_id', 1)
                // $absent = HRAttendance::where('attendance_type_id', 1)
                ->whereDate('attend_date', $s->attend_date)
                ->where('daytype_id', 1)
                ->where('staff_id', $st->id)
                ->get();
              $aaa += $absent->count();
              // dump($absent.' absent');
            }
            $percentage = (($workday - $fdl - $aaa) / $workday) * 100;
          } else {
            $workday = 0;
            // $fdl = 0;
            $percentage = 0;
          }

          //   'month' => $sm->format('M Y'),
          //   'percentage' => $percentage,
          //   'workdays' => $workday,
          //   'leaves' => $fdl,
          //   'absents' => $aaa,
          //   'working_days' => ($workday - $fdl - $aaa),
        }

        if ($percentage >= 80) {
          $attendance_percentage = 'box-green';
        } else {
          $attendance_percentage = 'box-red';
        }

        // M2 W5: precompute body-level lookups (were inline blade queries)
        $leavtype = $leav->belongstooptleavetype;
        $username = $staff?->hasmanylogin()?->where('active', 1)->first()?->username;
        $amend = $leav->hasmanyleaveamend()->get();

            $row = get_defined_vars();
            unset($row['approvals'], $row['grid'], $row['row'], $row['a']);
            $grid[$a->id] = $row;
        }
        return $grid;
    }
    /**
     * hr approval grid — per-row display data keyed by approval id.
     * Row logic spliced verbatim from the original blade (M2 W5).
     */
    public function hrGridData(Collection $approvals): array
    {
        $grid = [];
        $i = 1; // original blade pre-loop init (leave_type 9 branch re-assigns it)
        foreach ($approvals as $a) {
        $leav = HRLeave::find($a->leave_id);
        $staff = Staff::find($leav->staff_id);

        if (($leav->leave_type_id == 9) || ($leav->leave_type_id != 9 && $leav->half_type_id == 2) || ($leav->leave_type_id != 9 && $leav->half_type_id == 1)) {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y g:i a');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y g:i a');

          if ($leav->leave_type_id != 9) {
            if ($leav->half_type_id == 2) {
              $dper = $leav->period_day . ' Day';
            } elseif ($leav->half_type_id == 1) {
              $dper = $leav->period_day . ' Day';
            }
          } elseif ($leav->leave_type_id == 9) {
            $i = \Carbon\Carbon::parse($leav->period_time);
            $dper = $i->hour . ' hour, ' . $i->minute . ' minutes';
          }
        } else {
          $dts = \Carbon\Carbon::parse($leav->date_time_start)->format('j M Y ');
          $dte = \Carbon\Carbon::parse($leav->date_time_end)->format('j M Y ');
          $dper = $leav->period_day . ' day/s';
        }

        $z = \Carbon\Carbon::parse(now())->daysUntil($leav->date_time_start, 1)->count();

        if (3 >= $z && $z >= 2) {
          $u = 'table-warning';
        } elseif ($z < 2) {
          $u = 'table-danger';
        } else {
          $u = NULL;
        }

        // find leave backup if any
        $backup = $leav->hasmanyleaveapprovalbackup()->get();

        if ($backup->count()) {
          if (is_null($backup->first()->leave_status_id)) {
            $bapp = '<span class="text-warning" style="background-color:transparent;">Pending</span>';
            $bappb = false;
            $backup_person = "box-red"; // INDICATOR
          } else {
            $bapp = '<span class="text-success" style="background-color:transparent;">' . e(OptLeaveStatus::find($backup->first()->leave_status_id)->status) . '</span>';
            $bappb = true;
            $backup_person = "box-green"; // INDICATOR
          }
        } else {
          $bapp = '<span class="text-danger" style="background-color:transparent;">No Backup</span>';
          $bappb = true;
          $backup_person = "box-red";
        }

        // find leave supervisor if any
        $supervisor = $leav->hasmanyleaveapprovalsupervisor()->get();

        if ($supervisor->count()) {
          if (is_null($supervisor->first()->leave_status_id)) {
            $supp = '<span class="text-danger">Pending</span>';
            $suppb = false;
          } else {
            $supp = '<span class="text-success">' . e(OptLeaveStatus::find($supervisor->first()->leave_status_id)->status) . '</span>';
            $suppb = true;
          }
        } else {
          $supp = '<span class="text-success">No Supervisor</span>';
          $suppb = true;
        }

        // find leave hod if any
        $hod = $leav->hasmanyleaveapprovalhod()->get();

        if ($hod->count()) {
          if (is_null($hod->first()->leave_status_id)) {
            $hodd = '<span class="text-danger">Pending</span>';
            $hoddb = false;
          } else {
            $hodd = '<span class="text-success">' . e(OptLeaveStatus::find($hod->first()->leave_status_id)->status) . '</span>';
            $hoddb = true;
          }
        } else {
          $hodd = '<span class="text-success">No HOD</span>';
          $hoddb = true;
        }

        // find leave dir if any
        $dir = $leav->hasmanyleaveapprovaldir()->get();

        if ($dir->count()) {
          if (is_null($dir->first()->leave_status_id)) {
            $dirr = '<span class="text-danger">Pending</span>';
            $dirrb = false;
          } else {
            $dirr = '<span class="text-success">' . e(OptLeaveStatus::find($dir->first()->leave_status_id)->status) . '</span>';
            $dirrb = true;
          }
        } else {
          $dirr = '<span class="text-success">No Director</span>';
          $dirrb = true;
        }

        $hrremarksattendance = HRAttendance::where(function (Builder $query) use ($leav) {
          $query->whereDate('attend_date', '>=', $leav->date_time_start)
            ->whereDate('attend_date', '<=', $leav->date_time_end);
        })
          ->where('staff_id', $leav->staff_id)
          ->where(function (Builder $query) {
            $query->whereNotNull('remarks')->orWhereNotNull('hr_remarks');
          })
          // ->ddrawsql();
          ->get();

        $supervisor = $leav->hasmanyleaveapprovalsupervisor?->first();
        $hod = $leav->hasmanyleaveapprovalhod?->first();
        $director = $leav->hasmanyleaveapprovaldir?->first();
        $hr = $leav->hasmanyleaveapprovalhr?->first();

        // entitlement
        $annl = $staff->hasmanyleaveannual()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $mcel = $staff->hasmanyleavemc()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $matl = $staff->hasmanyleavematernity()?->where('year', Carbon::parse($leav->date_time_start)->format('Y'))->first();
        $replt = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_total) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $replb = $staff->hasmanyleavereplacement()?->selectRaw('SUM(leave_balance) as total')->where(function (Builder $query) use ($leav) {
          $query->whereDate('date_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
            ->whereDate('date_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
        })
          ->get();
        $upal = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->whereIn('leave_type_id', [3, 6])
          ->get();
        $mcupl = $staff->hasmanyleave()?->selectRaw('SUM(period_day) as total')
          ->where(function (Builder $query) use ($leav) {
            $query->whereDate('date_time_start', '>=', Carbon::parse($leav?->date_time_start)->startOfYear())
              ->whereDate('date_time_end', '<=', Carbon::parse($leav?->date_time_start)->endOfYear());
          })
          ->where(function (Builder $query) {
            $query->whereIn('leave_status_id', [5, 6])
              ->orWhereNull('leave_status_id');
          })
          ->where('leave_type_id', 11)
          ->get();

        // INDICATOR
        $leave_type_code = $leav->belongstooptleavetype?->leave_type_code;

        if (strpos($leave_type_code, 'EL') === false) {
          $sop = 'box-green';
        } else {
          $sop = 'box-red';
        }

        if (strpos($leave_type_code, 'UPL') === false) {
          $leave_type = 'box-green';
        } else {
          $leave_type = 'box-red';
        }

        if ($leave_type_code == 'AL' || $leave_type_code == 'NRL' || $leave_type_code == 'ML') {
          $support_doc = 'box-green';
        } else {
          if ($leav->softcopy != NUll) {
            $support_doc = 'box-green';
          } else {
            $support_doc = 'box-red';
          }
        }
        // -------------------------- CALCULATE ATTENDANCE PERCENTAGE --------------------------
        $st = Staff::find($leav->staff_id);
        $soy = now()->copy()->startOfYear();        // early this year
        $lsoy = $soy->copy()->subYear();          // early last year
        // dd($lsoy);
        // dd($lsoy->diffInMonths(now()));

        for ($no = 0; $no <= $soy->diffInMonths(now()); $no++) { // take only 2 years back
          $sm = $soy->copy()->addMonth($no);
          $em = $sm->copy()->endOfMonth();
          // dump([$sm, $em]);

          $sq = $st->hasmanyattendance()
            ->whereDate('attend_date', '>=', $sm)
            ->whereDate('attend_date', '<=', $em)
            ->where('daytype_id', 1)
            ->get();
          // ->ddRawSql();

          $fdl = 0;
          $aaa = 0;
          if ($sq->count()) {
            $workday = $sq->count();                            // working days
            // dump([$workday, $sm->format('M Y')]);

            foreach ($sq as $s) {
              $fulldayleave = $s->belongstoleave()?->where(function (Builder $query) {
                // $fulldayleave = HRLeave::where(function (Builder $query){
                $query->where('leave_type_id', '<>', 9)
                  ->where(function (Builder $query) {
                    $query->where('half_type_id', '<>', 2)
                      ->orWhereNull('half_type_id');
                  });
              })
                ->where(function (Builder $query) {
                  $query->whereIn('leave_status_id', [5, 6])
                    ->orWhereNull('leave_status_id');
                })
                ->where(function (Builder $query) use ($s) {
                  $query->whereDate('date_time_start', '<=', $s->attend_date)
                    ->WhereDate('date_time_end', '>=', $s->attend_date);
                })
                ->get();
              $fdl += $fulldayleave->count();
              // dump($fulldayleave->count().' fulldayleave count');

              $absent = $s->where('attendance_type_id', 1)
                // $absent = HRAttendance::where('attendance_type_id', 1)
                ->whereDate('attend_date', $s->attend_date)
                ->where('daytype_id', 1)
                ->where('staff_id', $st->id)
                ->get();
              $aaa += $absent->count();
              // dump($absent.' absent');
            }
            $percentage = (($workday - $fdl - $aaa) / $workday) * 100;
          } else {
            $workday = 0;
            // $fdl = 0;
            $percentage = 0;
          }

          //   'month' => $sm->format('M Y'),
          //   'percentage' => $percentage,
          //   'workdays' => $workday,
          //   'leaves' => $fdl,
          //   'absents' => $aaa,
          //   'working_days' => ($workday - $fdl - $aaa),
        }

        if ($percentage >= 80) {
          $attendance_percentage = 'box-green';
        } else {
          $attendance_percentage = 'box-red';
        }

        // M2 W5: precompute body-level lookups (were inline blade queries)
        $leavtype = $leav->belongstooptleavetype;
        $username = $staff?->hasmanylogin()?->where('active', 1)->first()?->username;
        $amend = $leav->hasmanyleaveamend()->get();

            $row = get_defined_vars();
            unset($row['approvals'], $row['grid'], $row['row'], $row['a']);
            $grid[$a->id] = $row;
        }
        return $grid;
    }
    /**
     * Check if current user can approve a given leave row.
     */
    public function canApprove(array $row, string $type, array $ctx): bool {
        $backupApproved = $row['backup']->count() ? !is_null($row['backup']->first()->leave_status_id) : true;
        if ($type === 'supervisor') {
            $visible = ($ctx['me3'] ?? false) ? (($row['ul'] ?? null) == ($ctx['us'] ?? null)) : true;
            return $visible && $backupApproved;
        }
        if ($type === 'hod') {
            $stadept = $row['stadept'] ?? null;
            $staffDiv = $row['staff']->div_id ?? null;
            $visible = $ctx['me5'] ?? false || (($ctx['deptid'] == 28 || $ctx['deptid'] == 21) && (in_array($stadept, [2,3,4,8,18,19,20,25,32,27,30,21,28]) || $staffDiv == 4)) || ($ctx['deptid'] == 6 && in_array($stadept, [6,7,3])) || ($ctx['deptid'] == 23 && in_array($stadept, [23,17,11,16])) || ($ctx['deptid'] == 1 && $stadept == 1) || ($ctx['deptid'] == 5 && $stadept == 5) || ($ctx['deptid'] == 12 && $stadept == 12) || ($ctx['deptid'] == 14 && $stadept == 14) || ($ctx['deptid'] == 15 && $stadept == 15) || ($ctx['deptid'] == 22 && $stadept == 22) || ($ctx['deptid'] == 24 && $stadept == 24);
            return $visible && $row['bappb'];
        }
        if ($type === 'dir') {
            return $backupApproved;
        }
        if ($type === 'hr') {
            return ($row['bappb'] ?? false) && ($row['suppb'] ?? false) && ($row['hoddb'] ?? false) && ($row['dirrb'] ?? false);
        }
        return false;
    }

    /**
     * Build table data rows for a given approval type.
     */
    public function tableData(string $type): array {
        $models = ['supervisor' => \App\Models\HumanResources\HRLeaveApprovalSupervisor::class, 'hod' => \App\Models\HumanResources\HRLeaveApprovalHOD::class, 'dir' => \App\Models\HumanResources\HRLeaveApprovalDirector::class, 'hr' => \App\Models\HumanResources\HRLeaveApprovalHR::class];
        if (!isset($models[$type])) {
            throw new \InvalidArgumentException('Unknown approval type: ' . $type);
        }
        $routes = ['supervisor' => 'leavestatus.supervisorstatus', 'hod' => 'leavestatus.hodstatus', 'dir' => 'leavestatus.dirstatus', 'hr' => 'leavestatus.hrstatus'];
        $approvals = $this->approvals($models[$type]);
        $grid = $this->gridData($approvals, $type);
        $ctx = $this->context();
        $ls = $ctx['ls'];
        $rows = [];
        foreach ($approvals as $a) {
            $row = $grid[$a->id] ?? [];
            if ($type === 'supervisor' && ($ctx['me3'] ?? false) && (($row['ul'] ?? null) != ($ctx['us'] ?? null))) {
                continue;
            }
            if ($type === 'hod') {
                $stadept = $row['stadept'] ?? null;
                $staffDiv = $row['staff']->div_id ?? null;
                $visible = $ctx['me5'] ?? false || (($ctx['deptid'] == 28 || $ctx['deptid'] == 21) && (in_array($stadept, [2,3,4,8,18,19,20,25,32,27,30,21,28]) || $staffDiv == 4)) || ($ctx['deptid'] == 6 && in_array($stadept, [6,7,3])) || ($ctx['deptid'] == 23 && in_array($stadept, [23,17,11,16])) || ($ctx['deptid'] == 1 && $stadept == 1) || ($ctx['deptid'] == 5 && $stadept == 5) || ($ctx['deptid'] == 12 && $stadept == 12) || ($ctx['deptid'] == 14 && $stadept == 14) || ($ctx['deptid'] == 15 && $stadept == 15) || ($ctx['deptid'] == 22 && $stadept == 22) || ($ctx['deptid'] == 24 && $stadept == 24);
                if (!$visible) {
                    continue;
                }
            }
            $can = $this->canApprove($row, $type, $ctx);
            $staff = $row['staff'] ?? $row['staff1'] ?? null;
            $leav = $row['leav'] ?? null;
            $staffName = $staff?->name ?? '';
            $reasonText = $leav->reason ?? '';
            $modalHtml = $can ? view('humanresources.hrdept.leave._approval_modal', array_merge($row, ['type' => $type, 'approval_id' => $a->id, 'ls' => $ls, 'route_name' => $routes[$type], 'approval_created_at' => $a->created_at, 'approval_remarks' => $a->remarks ?? '']))->render() : '';
            $rows[] = ['DT_RowClass' => $row['u'] ?? '', 'leave_no_link' => $leav ? '<a href="' . route('leave.show', $a->leave_id) . '">HR9-' . str_pad($leav->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $leav->leave_year . '</a>' : '', 'username' => $row['username'] ?? '', 'name' => '<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="' . e($staffName) . '">' . \Illuminate\Support\Str::words($staffName, 3, ' >') . '</span>', 'leave_type_code' => $row['leavtype']?->leave_type_code ?? '', 'reason' => '<span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="' . e($reasonText) . '">' . \Illuminate\Support\Str::limit($reasonText, 7, ' >') . '</span>', 'date_applied' => \Carbon\Carbon::parse($a->created_at)->format('j M Y'), 'dts' => $row['dts'] ?? '', 'dte' => $row['dte'] ?? '', 'dper' => $row['dper'] ?? '', 'bapp' => $row['bapp'] ?? '', 'supp' => $row['supp'] ?? '', 'hodd' => $row['hodd'] ?? '', 'dirr' => $row['dirr'] ?? '', 'approve' => $can ? '<button type="button" class="btn btn-sm btn-outline-secondary approve-btn" data-id="' . $a->id . '"><i class="bi bi-box-arrow-in-down"></i></button>' : '', 'modal_html' => $modalHtml];
        }
        return $rows;
    }
}
