<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\HumanResources\HRLeaveMC;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class MCLeaveController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		$years = HRLeaveMC::groupBy('year')->select('year')->orderBy('year', 'DESC')->get();

		$makeRow = function ($t, bool $active) {
			return (object) [
				'id' => $t->id,
				'username' => $active
					? $t->belongstostaff?->hasmanylogin()?->where('active', 1)->first()?->username
					: $t->belongstostaff?->hasmanylogin()?->first()?->username,
				'name' => $t->belongstostaff?->name,
				'entitlement_fmt' => $t->mc_leave . ' day/s',
				'adjustment_fmt' => $t->mc_leave_adjustment . ' day/s',
				'utilize_fmt' => $t->mc_leave_utilize . ' day/s',
				'balance_fmt' => $t->mc_leave_balance . ' day/s',
				'remarks' => $t->remarks,
			];
		};

		$activeRows = [];
		$inactiveRows = [];
		foreach ($years as $tp) {
			$rows = HRLeaveMC::where('year', $tp->year)->orderBy('year', 'DESC')->get();
			$activeRows[$tp->year] = $rows->filter(fn ($t) => $t->belongstostaff?->active == 1)->map(fn ($t) => $makeRow($t, true))->values();
			$inactiveRows[$tp->year] = $rows->filter(fn ($t) => $t->belongstostaff?->active != 1)->map(fn ($t) => $makeRow($t, false))->values();
		}

		return view('humanresources.hrdept.setting.mcleave.index', [
			'years' => $years,
			'activeRows' => $activeRows,
			'inactiveRows' => $inactiveRows,
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('humanresources.hrdept.setting.mcleave.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request): RedirectResponse
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRLeaveMC $mcleave): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeaveMC $mcleave): View
	{
		return view('humanresources.hrdept.setting.mcleave.edit', ['mcleave' => $mcleave]);
	}

	/**
	 * Update the specified resource in storage.
	 */
public function update(UpdateHRLeaveRequest $request, HRLeaveMC $mcleave): RedirectResponse
	{
		$mcleave->update($request->only(['mc_leave', 'mc_leave_adjustment', 'mc_leave_utilize', 'mc_leave_balance', 'remarks']));
		Session::flash('message', 'Data successfully updated!');
		return Redirect::route('mcleave.index');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeaveMC $mcleave): JsonResponse
	{
		//
	}
}
