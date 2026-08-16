<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load validation
use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRAttendance;
use App\Models\HumanResources\HRLeave;
use App\Models\HumanResources\OptDayType;
use App\Models\HumanResources\OptTcms;
use App\Models\Staff;

// load paginator
use Illuminate\Pagination\Paginator;

// load cursor pagination
use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load service
use App\Services\HumanResources\AttendanceService;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AttendanceController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request): View
	{
		// ini_set('max_execution_time', 60000000000);
		if ($request->date != NULL) {
			$selected_date = $request->date;
		} else {
			$current_time = now();
			$selected_date = $current_time->format('Y-m-d');
		}

		$attendance = HRAttendance::join('staffs', 'hr_attendances.staff_id', '=', 'staffs.id')
			->select('hr_attendances.id as id', 'staff_id', 'daytype_id', 'attendance_type_id', 'attend_date', 'in', 'break', 'resume', 'out', 'time_work_hour', 'work_hour', 'leave_id', 'hr_attendances.remarks as remarks', 'hr_attendances.hr_remarks as hr_remarks', 'exception', 'hr_attendances.created_at as created_at', 'hr_attendances.updated_at as updated_at', 'hr_attendances.deleted_at as deleted_at', 'staffs.name as name', 'staffs.restday_group_id as restday_group_id', 'staffs.active as active')
			->where('staffs.active', 1)
			->where('attend_date', $selected_date)
			// ->where(function(Builder $query) {
			// 	$query->whereDate('attend_date', '>=', '2023-01-01'
			// 	->whereDate('attend_date', '<=', '2023-12-31');
			// })
			->get();

		// who am i? ppl who can see only his staff in same department
		$staff = auth()->user()->belongstostaff;
		$me1 = $staff->div_id == 1;		// hod
		$me2 = $staff->div_id == 5;		// hod assistant
		$me3 = $staff->div_id == 4;		// supervisor
		// $me4 = $staff->div_id == 3;	// HR
		$me5 = $staff->authorise_id == 1;	// admin
		$me6 = $staff->div_id == 2;		// director

		$dept = $staff->belongstomanydepartment()->wherePivot('main', 1)->first();
		$deptid = $dept->id;
		$branch = $dept->branch_id;
		$category = $dept->category_id;

		// per-row grid computation moved out of the blade into the service
		$grid = (new AttendanceService())->gridData($attendance, [
			'me1' => $me1,
			'me2' => $me2,
			'me3' => $me3,
			'me5' => $me5,
			'me6' => $me6,
			'deptid' => $deptid,
			'branch' => $branch,
		]);

		return view('humanresources.hrdept.attendance.index', [
			'attendance' => $attendance,
			'selected_date' => $selected_date,
			'grid' => $grid,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request, HRAttendance $attendance): RedirectResponse
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRAttendance $attendance): View
	{
		return view('humanresources.hrdept.attendance.show', ['attendance' => $attendance]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRAttendance $attendance): View
	{
		$time_start_am = $time_end_am = $time_start_pm = $time_end_pm = '';

		$staff = Staff::find($attendance->staff_id);
		$department = $staff?->belongstomanydepartment()->wherePivot('main', 1)->first();

		$day_type = OptDayType::pluck('daytype', 'id')->sortKeys()->toArray();
		$tcms = OptTcms::pluck('leave_short', 'id')->sortKeys()->toArray();

		$login = $staff?->hasmanylogin()->where('active', '1')->get()->first();

		// preserved verbatim: original blade always took the raw value (condition used ||, effectively always true)
		$time_work_hour = $attendance->time_work_hour;

		$leaves = HRLeave::where('staff_id', $attendance->staff_id)
			->whereDate('date_time_start', '<=', $attendance->attend_date)
			->whereDate('date_time_end', '>=', $attendance->attend_date)
			->where(function (Builder $query) {
				$query->whereIn('leave_status_id', [5, 6])
					->orWhereNull('leave_status_id');
			})
			->orderBy('date_time_start', 'DESC')
			->get();

		if ($leaves->count()) {
			foreach ($leaves as $lv) {
				$leave = [$lv->id => 'HR9-' . str_pad($lv->leave_no, 5, '0', STR_PAD_LEFT) . '/' . $lv->leave_year];
			}
		} else {
			$leave = [];
		}

		if ($department) {
			$day_name = Carbon::parse($attendance->attend_date)->format('l');

			$working_hour = $department->belongstowhgroup()
				->where('effective_date_start', '<=', $attendance->attend_date)
				->where('effective_date_end', '>=', $attendance->attend_date);

			if (in_array($department->id, [19, 30])) {		// maintenance staff
				$working_hour = $working_hour->where('category', $day_name == 'Friday' ? 7 : 8);
			} elseif ($day_name == 'Friday') {
				$working_hour = $working_hour->where('category', 3);
			} else {
				$working_hour = $working_hour->where('category', '!=', 3);
			}

			$working_hour = $working_hour->first();

			if ($working_hour) {
				$time_start_am = Carbon::parse($working_hour->time_start_am)->format('H:i');
				$time_end_am = Carbon::parse($working_hour->time_end_am)->format('H:i');
				$time_start_pm = Carbon::parse($working_hour->time_start_pm)->format('H:i');
				$time_end_pm = Carbon::parse($working_hour->time_end_pm)->format('H:i');
			}
		}

		return view('humanresources.hrdept.attendance.edit', [
			'attendance' => $attendance,
			'staff' => $staff,
			'login' => $login,
			'day_type' => $day_type,
			'tcms' => $tcms,
			'time_work_hour' => $time_work_hour,
			'leaves' => $leaves,
			'leave' => $leave,
			'time_start_am' => $time_start_am,
			'time_end_am' => $time_end_am,
			'time_start_pm' => $time_start_pm,
			'time_end_pm' => $time_end_pm,
		]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(AttendanceRequestUpdate $request, HRAttendance $attendance): RedirectResponse
	{
		//dd($request->all());

		$exception = $request->has('exception') ? 1 : NULL;

		if ($request->remarks != NULL || $request->remarks != "") {
			$remarks = ucwords(Str::of($request->remarks)->lower());
		} else {
			$remarks = NULL;
		}

		if ($request->hr_remarks != NULL || $request->hr_remarks != "") {
			$hr_remarks = ucwords(Str::of($request->hr_remarks)->lower());
		} else {
			$hr_remarks = NULL;
		}

		$attendance->update([
			'daytype_id' => $request->daytype_id,
			'attendance_type_id' => $request->attendance_type_id,
			'leave_id' => $request->leave_id,
			'in' => $request->in,
			'break' => $request->break,
			'resume' => $request->resume,
			'out' => $request->out,
			'time_work_hour' => $request->time_work_hour,
			'remarks' => $remarks,
			'hr_remarks' => $hr_remarks,
			'exception' => $exception,
		]);

		$attendance->save();

		Session::flash('message', 'Data successfully updated!');
		return redirect()->route('attendance.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRAttendance $attendance): RedirectResponse
	{
		//
	}
}
