<?php
namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// load models
use App\Models\HumanResources\HROvertime;
use App\Models\HumanResources\HRAttendance;

// service
use App\Services\HumanResources\OvertimeService;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load cursor pagination
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;

// load support
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use Session;

class OvertimeController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|4|5,NULL', ['only' => ['create', 'store', 'index', 'show']]);      // all high management
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['edit', 'update', 'destroy']]);            // only hod and asst hod HR can access
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		Paginator::useBootstrapFive();

		$sa = HROvertime::SelectRaw('COUNT(`hr_overtimes`.`staff_id`) As totalstaff, YEAR(`hr_overtimes`.`ot_date`) AS `year`, MONTH(`hr_overtimes`.`ot_date`) AS `month`, `ot_date`')
						->where('active', 1)
						->groupByRaw('YEAR(ot_date)')
						->groupByRaw('MONTH(ot_date)')
						->orderByDesc('ot_date')
						// ->orderByDesc('month')
						// ->get();
						->paginate(1);
						// ->ddRawSql();
		// dd($sa);
		$overtime = HROvertime::select('*')
						->whereYear('ot_date', $sa->first()?->year)
						->whereMonth('ot_date', $sa->first()?->month)
						->where('active', 1)
						->orderBy('ot_date', 'DESC')
						->get();

		// per-user permission filter + date decoration (was inline in the blade)
		$overtime = app(OvertimeService::class)->indexData($overtime);

	return view('humanresources.hrdept.overtime.index', ['overtime' => $overtime, 'sa' => $sa]);
}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.overtime.create', app(OvertimeService::class)->createData());
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		// dd($request->all());
		foreach ($request->staff_id as $v) {

			if ($request->remark != NULL || $request->remark != "") {
				$remark = ucwords(Str::of($request->remark)->lower());
			} else {
				$remark = NULL;
			}

			HROvertime::create([
				'staff_id' => $v,
				'ot_date' => $request->ot_date,
				'overtime_range_id' => $request->overtime_range_id,
				'active' => 1,
				'assign_staff_id' => \Auth::user()->belongstostaff->id,
				'remark' => $remark,
			]);
		}
		Session::flash('message', 'Successfully Add Staff Overtime');
		return redirect()->route('overtime.index');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HROvertime $overtime): View
	{
		$range = $overtime->belongstoovertimerange;

		return view('humanresources.hrdept.overtime.show', [
			'overtime' => $overtime,
			'username' => $overtime->belongstostaff?->hasmanylogin()->where('active', 1)->first()?->username,
			'name' => $overtime->belongstostaff?->name,
			'ot_date_fmt' => Carbon::parse($overtime->ot_date)->format('j M Y'),
			'start_fmt' => $range ? Carbon::parse($range->start)->format('g:i a') : '-',
			'end_fmt' => $range ? Carbon::parse($range->end)->format('g:i a') : '-',
			'total_time' => $range?->total_time,
			'assign_name' => $overtime->belongstoassignstaff?->name,
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HROvertime $overtime): View
	{
		$service = app(OvertimeService::class);

		return view('humanresources.hrdept.overtime.edit', [
			'overtime' => $overtime,
			'staffs' => $service->staffOptions($overtime->staff_id),
			'overtimeRanges' => $service->overtimeRangeOptions(),
		]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HROvertime $overtime): RedirectResponse
	{
		if ($request->remark != NULL || $request->remark != "") {
			$remark = ucwords(Str::of($request->remark)->lower());
		} else {
			$remark = NULL;
		}

		$overtime->update([
			'staff_id' => $request->staff_id,
			'ot_date' => $request->ot_date,
			'overtime_range_id' => $request->overtime_range_id,
			'assign_staff_id' => \Auth::user()->belongstostaff->id,
			'remark' => $remark,
		]);

		$overtime->save();

		Session::flash('message', 'Successfully Update Staff Overtime');
		return redirect()->route('overtime.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HROvertime $overtime): JsonResponse
	{
		// remove from attendance
		$r = HRAttendance::where('overtime_id', $overtime->id)->get();

		foreach ($r as $c) {
			HRAttendance::where('id', $c->id)->update(['overtime_id' => null]);
		}

		$overtime->update(['active' => NULL]);

		return response()->json([
			'message' => 'Overtime Deleted',
			'status' => 'success'
		]);
	}
}
