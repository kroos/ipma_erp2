<?php

namespace App\Services\HumanResources;

// load db facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load collection
use Illuminate\Support\Collection;

// load models
use App\Models\HumanResources\HRAttendanceRemark;
use App\Models\HumanResources\HRHolidayCalendar;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\HROutstation;
use App\Models\HumanResources\HROutstationAttendance;
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;

// load helper
use App\Helpers\UnavailableDateTime;

// load Carbon
use Carbon\Carbon;

/**
 * AttendanceService — per-row attendance grid data.
 *
 * Extracted from the 4,110-line attendance/index blade so the grid
 * computation (authorisation, working hour, leave, overtime, outstation,
 * holiday, remark and leave-approval lookups + backfill updates) lives in
 * business logic instead of the view.
 */
class AttendanceService
{
	/**
	 * Build the authorised-view grid data for the attendance index.
	 *
	 * @param  \Illuminate\Support\Collection<int, \App\Models\HumanResources\HRAttendance>  $attendance
	 * @param  array{me1: bool, me2: bool, me3: bool, me5: bool, me6: bool, deptid: int, branch: ?int}  $ctx
	 * @return array<int, array<string, mixed>>  display vars keyed by attendance id (authorised rows only)
	 */
	public function gridData(Collection $attendance, array $ctx): array
	{
		$me1 = $ctx['me1'];
		$me2 = $ctx['me2'];
		$me3 = $ctx['me3'];
		$me5 = $ctx['me5'];
		$me6 = $ctx['me6'];
		$deptid = $ctx['deptid'];
		$branch = $ctx['branch'];

		$grid = [];

		// per-staff main department map — kills the N+1 dept query the blade used to run 2-3x per row
		$mainDept = DB::table('pivot_staff_pivotdepts as psp')
				->join('pivot_dept_cate_branches as d', 'd.id', '=', 'psp.pivot_dept_id')
				->where('psp.main', 1)
				->whereIn('psp.staff_id', $attendance->pluck('staff_id')->unique())
				->select('psp.staff_id', 'd.id', 'd.category_id', 'd.branch_id')
				->get()
				->keyBy('staff_id');

		foreach ($attendance as $s) {
			$ha = false;
			$dept = $mainDept[$s->staff_id] ?? null;

			// setting for authorised views
			if ($me1) {																				// hod
				if ($deptid == 21) {																// hod | dept prod A
					$ha = $dept?->id == $deptid || $dept?->category_id == 2;
				} elseif($deptid == 28) {															// hod | not dept prod A | dept prod B
					$ha = $dept?->id == $deptid || $dept?->category_id == 2;
				} elseif($deptid == 14) {															// hod | not dept prod A | not dept prod B | HR
					$ha = true;
				} elseif($deptid == 6) {															// hod | not dept prod A | not dept prod B | not HR | cust serv
					$ha = $dept?->id == $deptid || $dept?->id == 7;
				} elseif ($deptid == 23) {															// hod | not dept prod A | not dept prod B | not HR | not cust serv | puchasing
					$ha = $dept?->id == $deptid || $dept?->id == 16 || $dept?->id == 17;
				} else {																			// hod | not dept prod A | not dept prod B | not HR | not cust serv | not puchasing | other dept
					$ha = $dept?->id == $deptid;
				}
			} elseif($me2) {																		// not hod | asst hod
				if($deptid == 14) {																	// not hod | not dept prod A | not dept prod B | HR
					$ha = true;
				} elseif($deptid == 6) {															// not hod | not dept prod A | not dept prod B | not HR | cust serv
					$ha = $dept?->id == $deptid || $dept?->id == 7;
				}
			} elseif($me3) {																		// not hod | not asst hod | supervisor
				if($branch == 1) {																	// not hod | not asst hod | supervisor | branch A
					$ha = $dept?->id == $deptid || ($dept?->category_id == 2 && $dept?->branch_id == $branch);
				} elseif ($branch == 2) {															// not hod | not asst hod | supervisor | not branch A | branch B
					$ha = $dept?->id == $deptid || ($dept?->category_id == 2 && $dept?->branch_id == $branch);
				}
			} elseif($me6) {																		// not hod | not asst hod | not supervisor | director
				$ha = true;
			} elseif($me5) {																		// not hod | not asst hod | not supervisor | not director | admin
				$ha = true;
			} else {
				$ha = false;
			}
			// done setting for authorised view for hod, asst hod, supervisor, director and hr


			if (!$ha) {
				continue;
			}

			/////////////////////////////
			// to determine working hour of each user
			$wh = UnavailableDateTime::workinghourtime($s->attend_date, $s->belongstostaff->id)->first();

			// looking for leave of each staff
			// $l = $s->belongstostaff->hasmanyleave()
			$l = HRLeave::where('staff_id', $s->staff_id)
					->where(function (Builder $query) {
						$query->whereIn('leave_status_id', [5, 6])->orWhereNull('leave_status_id');
					})
					->where(function (Builder $query) use ($s){
						$query->whereDate('date_time_start', '<=', $s->attend_date)
						->whereDate('date_time_end', '>=', $s->attend_date);
					})
					->first();
			// dump($l);

			$o = HROvertime::where([['staff_id', $s->staff_id], ['ot_date', $s->attend_date], ['active', 1]])->first();

			$os = HROutstation::where('staff_id', $s->staff_id)
					->where('active', 1)
					->where(function (Builder $query) use ($s){
						$query->whereDate('date_from', '<=', $s->attend_date)
						->whereDate('date_to', '>=', $s->attend_date);
					})
					->get();

			$in = Carbon::parse($s->in)->equalTo('00:00:00');
			$break = Carbon::parse($s->break)->equalTo('00:00:00');
			$resume = Carbon::parse($s->resume)->equalTo('00:00:00');
			$out = Carbon::parse($s->out)->equalTo('00:00:00');

			// looking for RESTDAY, WORKDAY & HOLIDAY
			$sun = Carbon::parse($s->attend_date)->dayOfWeek == 0;		// sunday
			$sat = Carbon::parse($s->attend_date)->dayOfWeek == 6;		// saturday
			$hdate = HRHolidayCalendar::
					where(function (Builder $query) use ($s){
						$query->whereDate('date_start', '<=', $s->attend_date)
						->whereDate('date_end', '>=', $s->attend_date);
					})
					->get();

			if($hdate->isNotEmpty()) {											// date holiday
				$dayt = OptDayType::find(3)->daytype;							// show what day: HOLIDAY
				$dtype = false;
				$s->update(['daytype_id' => 3]);
			} elseif($hdate->isEmpty()) {										// date not holiday
				if(Carbon::parse($s->attend_date)->dayOfWeek == 0) {			// sunday
					$dayt = OptDayType::find(2)->daytype;
					$dtype = false;
					$s->update(['daytype_id' => 2]);
				} elseif(Carbon::parse($s->attend_date)->dayOfWeek == 6) {		// saturday
					$sat = $s->belongstostaff->belongstorestdaygroup?->hasmanyrestdaycalendar()->whereDate('saturday_date', $s->attend_date)->first();
					if($sat) {													// determine if user belongs to sat group restday
						$dayt = OptDayType::find(2)->daytype;					// show what day: RESTDAY
						$dtype = false;
						$s->update(['daytype_id' => 2]);
					} else {
						$dayt = OptDayType::find(1)->daytype;					// show what day: WORKDAY
						$dtype = true;
						$s->update(['daytype_id' => 1]);
					}
				} else {														// all other day is working day
					$dayt = OptDayType::find(1)->daytype;						// show what day: WORKDAY
					$dtype = true;
					$s->update(['daytype_id' => 1]);
				}
			}

			// detect all
			if ($os->isNotEmpty()) {																							// outstation |
				if ($dtype) {																									// outstation | working
					if ($l) {																									// outstation | working | leave
						if ($in) {																								// outstation | working | leave | no in
							if ($break) {																						// outstation | working | leave | no in | no break
								if ($resume) {																					// outstation | working | leave | no in | no break | no resume
									if ($out) {																					// outstation | working | leave | no in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | no in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | leave | no in | no break | resume
									if ($out) {																					// outstation | working | leave | no in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | no in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | working | leave | no in | break
								if ($resume) {																					// outstation | working | leave | no in | break | no resume
									if ($out) {																					// outstation | working | leave | no in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | no in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | leave | no in | break | resume
									if ($out) {																					// outstation | working | leave | no in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | no in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// outstation | working | leave | in
							if ($break) {																						// outstation | working | leave | in | no break
								if ($resume) {																					// outstation | working | leave | in | no break | no resume
									if ($out) {																					// outstation | working | leave | in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | leave | in | no break | resume
									if ($out) {																					// outstation | working | leave | in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | working | leave | in | break
								if ($resume) {																					// outstation | working | leave | in | break | no resume
									if ($out) {																					// outstation | working | leave | in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | leave | in | break | resume
									if ($out) {																					// outstation | working | leave | in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | leave | in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					} else {																									// outstation | working | no leave
						if ($in) {																								// outstation | working | no leave | no in
							if ($break) {																						// outstation | working | no leave | no in | no break
								if ($resume) {																					// outstation | working | no leave | no in | no break | no resume
									if ($out) {																					// outstation | working | no leave | no in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | no in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | no leave | no in | no break | resume
									if ($out) {																					// outstation | working | no leave | no in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | no in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | working | no leave | no in | break
								if ($resume) {																					// outstation | working | no leave | no in | break | no resume
									if ($out) {																					// outstation | working | no leave | no in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | no in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | no leave | no in | break | resume
									if ($out) {																					// outstation | working | no leave | no in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | no in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
												} else {
												$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											}
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// outstation | working | no leave | in
							if ($break) {																						// outstation | working | no leave | in | no break
								if ($resume) {																					// outstation | working | no leave | in | no break | no resume
									if ($out) {																					// outstation | working | no leave | in | no break | no resume | no out
										if (Carbon::parse(now())->gt($s->attend_date)) {
											if (is_null($s->attendance_type_id)) {
												$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
											} else {
												$ll = e($s->belongstoopttcms->leave);
											}
										} else {
											if (is_null($s->attendance_type_id)) {
												$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
											} else {
												$ll = e($s->belongstoopttcms->leave);
											}
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | no leave | in | no break | resume
									if ($out) {																					// outstation | working | no leave | in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | working | no leave | in | break
								if ($resume) {																					// outstation | working | no leave | in | break | no resume
									if ($out) {																					// outstation | working | no leave | in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | working | no leave | in | break | resume
									if ($out) {																					// outstation | working | no leave | in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
												} else {
												$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											}
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | working | no leave | in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					}
				} else {																										// outstation | no working
					if ($l) {																									// outstation | no working | leave
						if ($in) {																								// outstation | no working | leave | no in
							if ($break) {																						// outstation | no working | leave | no in | no break
								if ($resume) {																					// outstation | no working | leave | no in | no break | no resume
									if ($out) {																					// outstation | no working | leave | no in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | no in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | leave | no in | no break | resume
									if ($out) {																					// outstation | no working | leave | no in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | no in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | no working | leave | no in | break
								if ($resume) {																					// outstation | no working | leave | no in | break | no resume
									if ($out) {																					// outstation | no working | leave | no in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | no in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | leave | no in | break | resume
									if ($out) {																					// outstation | no working | leave | no in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | no in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// outstation | no working | leave | in
							if ($break) {																						// outstation | no working | leave | in | no break
								if ($resume) {																					// outstation | no working | leave | in | no break | no resume
									if ($out) {																					// outstation | no working | leave | in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | leave | in | no break | resume
									if ($out) {																					// outstation | no working | leave | in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | no working | leave | in | break
								if ($resume) {																					// outstation | no working | leave | in | break | no resume
									if ($out) {																					// outstation | no working | leave | in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | leave | in | break | resume
									if ($out) {																					// outstation | no working | leave | in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | leave | in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					} else {																									// outstation | no working | no leave
						if ($in) {																								// outstation | no working | no leave | no in
							if ($break) {																						// outstation | no working | no leave | no in | no break
								if ($resume) {																					// outstation | no working | no leave | no in | no break | no resume
									if ($out) {																					// outstation | no working | no leave | no in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | no in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | no leave | no in | no break | resume
									if ($out) {																					// outstation | no working | no leave | no in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | no in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | no working | no leave | no in | break
								if ($resume) {																					// outstation | no working | no leave | no in | break | no resume
									if ($out) {																					// outstation | no working | no leave | no in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | no in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | no leave | no in | break | resume
									if ($out) {																					// outstation | no working | no leave | no in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | no in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// outstation | no working | no leave | in
							if ($break) {																						// outstation | no working | no leave | in | no break
								if ($resume) {																					// outstation | no working | no leave | in | no break | no resume
									if ($out) {																					// outstation | no working | no leave | in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | no leave | in | no break | resume
									if ($out) {																					// outstation | no working | no leave | in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// outstation | no working | no leave | in | break
								if ($resume) {																					// outstation | no working | no leave | in | break | no resume
									if ($out) {																					// outstation | no working | no leave | in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// outstation | no working | no leave | in | break | resume
									if ($out) {																					// outstation | no working | no leave | in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// outstation | no working | no leave | in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.e(OptTcms::find(4)->leave).'</a>';					// outstation
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($os) {
											$s->update(['outstation_id' => $os->first()->id, 'attendance_type_id' => 4]);
										} else {
											$s->update(['outstation_id' => NULL, 'attendance_type_id' => NULL]);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					}
				}
			} else {																											// no outstation
				if ($dtype) {																									// no outstation | working
					if ($l) {																									// no outstation | working | leave
						if ($in) {																								// no outstation | working | leave | no in
							if ($break) {																						// no outstation | working | leave | no in | no break
								if ($resume) {																					// no outstation | working | leave | no in | no break | no resume
									if ($out) {																					// no outstation | working | leave | no in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | no in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | working | leave | no in | no break | resume
									if ($out) {																					// no outstation | working | leave | no in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | no in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype?->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation | working | leave | no in | break
								if ($resume) {																					// no outstation | working | leave | no in | break | no resume
									if ($out) {																					// no outstation | working | leave | no in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | no in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | working | leave | no in | break | resume
									if ($out) {																					// no outstation | working | leave | no in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | no in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// no outstation | working | leave | in
							if ($break) {																						// no outstation | working | leave | in | no break
								if ($resume) {																					// no outstation | working | leave | in | no break | no resume
									if ($out) {																					// no outstation | working | leave | in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | working | leave | in | no break | resume
									if ($out) {																					// no outstation | working | leave | in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation | working | leave | in | break
								if ($resume) {																					// no outstation | working | leave | in | break | no resume
									if ($out) {																					// no outstation | working | leave | in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | working | leave | in | break | resume
									if ($out) {																					// no outstation | working | leave | in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | leave | in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = $l->belongstooptleavetype->leave_type_code;
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					} else {																									// no outstation | working | no leave
						if ($in) {																								// no outstation | working | no leave | no in
							if ($break) {																						// no outstation | working | no leave | no in | no break
								if ($resume) {																					// no outstation | working | no leave | no in | no break | no resume
									if ($out) {																					// no outstation | working | no leave | no in | no break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(1)->leave.'</a>';					// absent
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | no leave | no in | no break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | working | no leave | no in | no break | resume
									if ($out) {																					// no outstation | working | no leave | no in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					//  pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | no leave | no in | no break | resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation | working | no leave | no in | break
								if ($resume) {																					// no outstation | working | no leave | no in | break | no resume
									if ($out) {																					// no outstation | working | no leave | no in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation |  outstation | working | no leave | no in | break | no resume | out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation |  outstation | working | no leave | no in | break | resume
									if ($out) {																					// no outstation |  outstation | working | no leave | no in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation |  outstation | working | no leave | no in | break | resume | out
										if (is_null($s->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
											} else {
												$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											}
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// no outstation |  outstation | working | no leave | in
							if ($break) {																						// no outstation |  outstation | working | no leave | in | no break
								if ($resume) {																					// no outstation |  outstation | working | no leave | in | no break | no resume
									if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | no resume | no out
										if (Carbon::parse(now())->gt($s->attend_date)) {
											if (is_null($s->attendance_type_id)) {
												$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
											} else {
												$ll = e($s->belongstoopttcms->leave);
											}
										} else {
											$ll = false;
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation |  outstation | working | no leave | in | no break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation |  outstation | working | no leave | in | no break | resume
									if ($out) {																					// no outstation |  outstation | working | no leave | in | no break | resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation |  outstation | working | no leave | in | no break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation |  outstation | working | no leave | in | break
								if ($resume) {																					// no outstation |  outstation | working | no leave | in | break | no resume
									if ($out) {																					// no outstation |  outstation | working | no leave | in | break | no resume | no out
										if (is_null($s->attendance_type_id)) {
											$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | no leave | in | break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | working | no leave | in | break | resume
									if ($out) {																					// no outstation | working | no leave | in | break | resume | no out
										if (is_null($s->attendance_type_id)) {
											if ($break == $resume) {															// check for break and resume is the same value
												$ll = '<a href="'.route('attendance.edit', $s->id).'">'.OptTcms::find(2)->leave.'</a>';					// half absent
											} else {
												$ll = '<a href="'.route('attendance.edit', $s->id).'">Check</a>';					// pls check
											}
										} else {
											$ll = e($s->belongstoopttcms->leave);
										}
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | working | no leave | in | break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												$s->update(['leave_id' => $l->id]);
												// $s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					}
				} else {																										// no outstation | no working
					if ($l) {																									// no outstation | no working | leave
						if ($in) {																								// no outstation | no working | leave | no in
							if ($break) {																						// no outstation | no working | leave | no in | no break
								if ($resume) {																					// no outstation | no working | leave | no in | no break | no resume
									if ($out) {																					// no outstation | no working | leave | no in | no break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | no in | no break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | leave | no in | no break | resume
									if ($out) {																					// no outstation | no working | leave | no in | no break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | no in | no break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation | no working | leave | no in | break
								if ($resume) {																					// no outstation | no working | leave | no in | break | no resume
									if ($out) {																					// no outstation | no working | leave | no in | break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | no in | break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | leave | no in | break | resume
									if ($out) {																					// no outstation | no working | leave | no in | break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | no in | break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// no outstation | no working | leave | in
							if ($break) {																						// no outstation | no working | leave | in | no break
								if ($resume) {																					// no outstation | no working | leave | in | no break | no resume
									if ($out) {																					// no outstation | no working | leave | in | no break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | in | no break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | leave | in | no break | resume
									if ($out) {																					// no outstation | no working | leave | in | no break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | in | no break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation | no working | leave | in | break
								if ($resume) {																					// no outstation | no working | leave | in | break | no resume
									if ($out) {																					// no outstation | no working | leave | in | break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | in | break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | leave | in | break | resume
									if ($out) {																					// no outstation | no working | leave | in | break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | leave | in | break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					} else {																									// no outstation | no working | no leave
						if ($in) {																								// no outstation | no working | no leave | no in
							if ($break) {																						// no outstation | no working | no leave | no in | no break
								if ($resume) {																					// no outstation | no working | no leave | no in | no break | no resume
									if ($out) {																					// no outstation | no working | no leave | no in | no break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | no in | no break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | no leave | no in | no break | resume
									if ($out) {																					// no outstation | no working | no leave | no in | no break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | no in | no break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation | no working | no leave | no in | break
								if ($resume) {																					// no outstation | no working | no leave | no in | break | no resume
									if ($out) {																					// no outstation | no working | no leave | no in | break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | no in | break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | no leave | no in | break | resume
									if ($out) {																					// no outstation | no working | no leave | no in | break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | no in | break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						} else {																								// no outstation | no working | no leave | in
							if ($break) {																						// no outstation | no working | no leave | in | no break
								if ($resume) {																					// no outstation | no working | no leave | in | no break | no resume
									if ($out) {																					// no outstation | no working | no leave | in | no break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | in | no break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | no leave | in | no break | resume
									if ($out) {																					// no outstation | no working | no leave | in | no break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | in | no break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							} else {																							// no outstation | no working | no leave | in | break
								if ($resume) {																					// no outstation | no working | no leave | in | break | no resume
									if ($out) {																					// no outstation | no working | no leave | in | break | no resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | in | break | no resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								} else {																						// no outstation | no working | no leave | in | break | resume
									if ($out) {																					// no outstation | no working | no leave | in | break | resume | no out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									} else {																					// no outstation | no working | no leave | in | break | resume | out
										$ll = false;
										if ($o) {																									// overtime
											if (is_null($s->overtime_id)) {										// update this row if overtime_id is null with overtime id
												$s->update(['overtime_id' => $o->id]);
											} else {
												$s->update(['overtime_id' => null]);
											}
										}
										if($l) {																									// leave
											if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
												$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
												// $s->update(['leave_id' => $l->id]);
												$s->update(['leave_id' => NULL]);
											} else {															// otherwise just show the leave
												// $lea = $s->belongstoleave->id;
												$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
											}
										} else {
											$lea = NULL;
											$s->update(['leave_id' => NULL]);
										}
									}
								}
							}
						}
					}
				}
			}

			// if($l) {
			// 	if (is_null($s->leave_id)) {										// update this row if leave_id is null with leave id
			// 		$lea = '<a href="'.route('hrleave.show', $l->id).'">'.'HR9-'.e(str_pad($l->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($l->leave_year).'</a>';
			// 		$s->update(['leave_id' => $l->id]);
			// 	} else {															// otherwise just show the leave
			// 		// $lea = $s->belongstoleave->id;
			// 		$lea = '<a href="'.route('hrleave.show', $s->leave_id).'">'.'HR9-'.e(str_pad($s->belongstoleave->leave_no,5,'0',STR_PAD_LEFT)).'/'.e($s->belongstoleave->leave_year).'</a>';
			// 	}
			// } else {
			// 	$lea = NULL;
			// 	$s->update(['leave_id' => NULL]);
			// }
			$attrem = HRAttendanceRemark::where(function(Builder $query) use ($s) {
												$query->whereDate('date_from', '<=', $s->attend_date)
												->whereDate('date_to', '>=', $s->attend_date);
											})
											->where('staff_id', $s->staff_id)
											->get();
			if ($attrem->count()) {
				if($s->daytype_id == 1) {
					$s->update([
						'remarks' => $attrem->first()?->attendance_remarks,
						'hr_remarks' => $attrem->first()?->hr_attendance_remarks,
					]);
				}
			}

			$outatt = HROutstationAttendance::where([['date_attend', $s->attend_date], ['staff_id', $s->staff_id], ['confirm', 1]])->get();
			if($outatt->count()) {
				if ($s->daytype_id == 1 && is_null($s->leave_id) && !is_null($s->outstation_id)) {
					$s->update([
						'in' => $outatt->first()?->in,
						'out' => $outatt->first()?->out,
					]);
				}
			}

			// checking for aprroved, pending or reject leaves
			if ($l) {
				// $leaveApproval = Staff::find($l->staff_id)->belongstoleaveapprovalflow
				$backup = $l->hasmanyleaveapprovalbackup()?->get();
				$supervisor = $l->hasmanyleaveapprovalsupervisor()?->get();
				$hod = $l->hasmanyleaveapprovalhod()?->get();
				$director = $l->hasmanyleaveapprovaldir()?->get();
				$hr = $l->hasmanyleaveapprovalhr()?->get();
				// if ($backup) {
				// 	if ($backup->first()?->leave_status_id == 5) {
				// 		$back = true;
				// 	} else {
				// 		$back = false;
				// 	}
				// }
				// if ($supervisor) {
				// 	if ($supervisor->first()?->leave_status_id == 5) {
				// 		$superv = true;
				// 	} else {
				// 		$superv = false;
				// 	}
				// }
				// if ($hod) {
				// 	if ($hod->first()?->leave_status_id == 5) {
				// 		$hodi = true;
				// 	} else {
				// 		$hodi = false;
				// 	}
				// }
				// if ($director) {
				// 	if ($director->first()?->leave_status_id == 5) {
				// 		$direct = true;
				// 	} else {
				// 		$direct = false;
				// 	}
				// }
				if ($hr) {
					if ($hr->first()?->leave_status_id == 5) {
						$hri = true;
					} else {
						$hri = false;
					}
				}
				// if ($back && $superv && $hodi && $direct && $hri) {
				if ($hri) {
					$leaveIndicator = 'style="background-color: #d5f5e3;"';
				} else {
					$leaveIndicator = 'style="background-color: #fadbd8;"';
				}
			}




			// out-punch colour class (was inline echo in the blade row)
			ob_start();

								if($out) {																													// no punch out
									echo 'text-info';
								} else {																													// punch out
									if($o) {																												// punch out | OT
										if (Carbon::parse($s->out)->gt($o->belongstoovertimerange?->end)) {													// punch out | OT | out lt OT
											echo 'text-d ot';
										} else {																											// punch out | OT | OT lt out
											if (Carbon::parse($s->out)->lt($o->belongstoovertimerange?->end)) {												// punch out | OT | OT gt out
												echo 'text-danger ot';
											}
										}
									} else {																												// punch out | no OT
										if (Carbon::parse($s->out)->lt($wh->time_end_pm)) {																	// punch out | no OT | out lt working hour
											echo 'text-danger wh';
										} else {																											// punch out | no OT | out gt working hour
											if (Carbon::parse($s->out)->gt($wh->time_end_pm)) {
												echo 'text-d wh';
											}
										}
									}
								}


			$outClass = trim(ob_get_clean());

			// overtime id backfill (was inline in the blade row — preserved side effect)
							if(is_null($s->overtime_id)) {
								if ($o) {
									$s->update(['overtime_id' => $o->id]);
								} else {
									$s->update(['overtime_id' => NULL]);
								}
							}


			$grid[$s->id] = [
				'ha' => $ha,
				'wh' => $wh ?? null,
				'l' => $l ?? null,
				'o' => $o ?? null,
				'os' => $os ?? null,
				'in' => $in ?? null,
				'break' => $break ?? null,
				'resume' => $resume ?? null,
				'out' => $out ?? null,
				'dayt' => $dayt ?? null,
				'll' => $ll ?? null,
				'lea' => $lea ?? null,
				'leaveIndicator' => $leaveIndicator ?? null,
				'username' => $s->belongstostaff?->hasmanylogin()->where('active', 1)->first()?->username,
				'ot_total' => $o?->belongstoovertimerange?->where('active', 1)->first()->total_time,
				'os_customer' => $os?->first()?->belongstocustomer?->customer,
				'out_class' => $outClass,
			];
		}

		return $grid;
	}

	/**
	 * Enrich the daily-report "absent" rows with display fields that the
	 * blade used to compute via inline model queries (leave type code, leave
	 * number, tcms status). Mutates and returns the same collection.
	 *
	 * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $rows
	 * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>
	 */
	public function enrichAbsent(Collection $rows): Collection
	{
		foreach ($rows as $row) {
			$row->status = null;
			$row->remark = $row->remarks;
			$row->leave_number = null;
			$row->leave_record_id = null;

			if ($row->leave_id != null) {
				$leave = HRLeave::join('option_leave_types', 'hr_leaves.leave_type_id', '=', 'option_leave_types.id')
					->where('hr_leaves.id', '=', $row->leave_id)
					->select('hr_leaves.id as leave_id', 'hr_leaves.leave_no', 'hr_leaves.leave_year', 'option_leave_types.leave_type_code', 'hr_leaves.reason')
					->first();

				if ($leave) {
					$row->status = $leave->leave_type_code;
					$row->remark = $row->remarks;
					$row->leave_number = 'HR9-' . str_pad($leave->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $leave->leave_year;
					$row->leave_record_id = $leave->leave_id;
				}
			} elseif ($row->attendance_type_id != null) {
				$row->status = OptTcms::find($row->attendance_type_id)?->leave;
			}
		}

		return $rows;
	}

	/**
	 * Build the daily-report "late" rows from the already-queried late staff
	 * collection, filtered to the late ids and enriched with the display fields
	 * the blade used to compute inline (status, remark, leave number, in time).
	 *
	 * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $staffsLate
	 * @param  array<int, int>  $lateIds
	 * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>
	 */
	public function lateRows(Collection $staffsLate, array $lateIds): Collection
	{
		$rows = $staffsLate->filter(fn ($r) => in_array($r->StaffID, $lateIds))->values();

		foreach ($rows as $row) {
			$row->in = Carbon::parse($row->in)->format('h:i a');
			$row->status = null;
			$row->leave_number = null;
			$row->leave_record_id = null;

			if ($row->leave_id != null) {
				$leave = HRLeave::join('option_leave_types', 'hr_leaves.leave_type_id', '=', 'option_leave_types.id')
					->where('hr_leaves.id', '=', $row->leave_id)
					->select('hr_leaves.id as leave_id', 'hr_leaves.leave_no', 'hr_leaves.leave_year', 'option_leave_types.leave_type_code', 'hr_leaves.reason')
					->first();

				if ($leave) {
					$row->status = $leave->leave_type_code;
					$row->remark = $leave->reason;
					$row->leave_number = 'HR9-' . str_pad($leave->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $leave->leave_year;
					$row->leave_record_id = $leave->leave_id;
				} else {
					$row->status = null;
					$row->remark = $row->remarks;
				}
			} else {
				$row->status = $row->attendance_type_id != null ? OptTcms::find($row->attendance_type_id)?->leave : null;
				$row->remark = $row->remarks;
			}
		}

		return $rows;
	}

	/**
	 * Enrich the daily-report "outstation" rows with the display fields the
	 * blade used to compute inline (outstation customer / remark, tcms status).
	 * Mutates and returns the same collection.
	 *
	 * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $rows
	 * @return \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>
	 */
	public function enrichOutstation(Collection $rows): Collection
	{
		foreach ($rows as $row) {
			$row->status = null;
			$row->contact = null;

			if ($row->outstation_id != null) {
				$out = HROutstation::leftjoin('customers', 'hr_outstations.customer_id', '=', 'customers.id')
					->where('hr_outstations.id', '=', $row->outstation_id)
					->where('hr_outstations.active', '=', 1)
					->select('customers.customer', 'hr_outstations.remarks', 'hr_outstations.customer_id')
					->first();

				$row->status = 'OUTSTATION';
				$row->remark = ($out && $out->customer_id != null) ? $out->customer : $out?->remarks;
			} else {
				$row->status = $row->attendance_type_id != null ? OptTcms::find($row->attendance_type_id)?->leave : null;
				$row->remark = $row->remarks;
			}
		}

		return $rows;
	}
}
