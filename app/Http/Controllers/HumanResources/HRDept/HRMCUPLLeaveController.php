<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;

// models
use App\Models\Staff;
use App\Models\HumanResources\HRLeave;

// services
use App\Services\HumanResources\EntitlementService;

use Illuminate\Database\Eloquent\Builder;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use Session;
use Carbon\Carbon;

class HRMCUPLLeaveController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['index', 'show']]);
		$this->middleware('highMgmtAccessLevel1:1|5,14', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		$config = [
			'title' => 'Unpaid Medical Certificate Leave',
			'table_title' => 'Unpaid Leave Entitlement',
			'variant' => 'upl',
			'model' => HRLeave::class,
			'endpoint' => 'hrmcuplleave.index',
			'leave_type_ids' => [11],
			'columns' => ['ID', 'Name', 'Leave ID', 'Leave Type', 'Duration', 'From', 'To', 'Remarks'],
		];

		$rows = app(EntitlementService::class)->uplRows($config['leave_type_ids']);

		return view('humanresources.hrdept.entitlement.index', compact('config', 'rows'));
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		//
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
	public function show(HRLeaveReplacement $hrreplacementleave): View
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeaveReplacement $hrreplacementleave): View
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, HRLeaveReplacement $hrreplacementleave): RedirectResponse
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Request $request, HRLeaveReplacement $hrreplacementleave): JsonResponse
	{
		//
	}

}
