<?php

namespace App\Http\Controllers\HumanResources\HRDept;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load service
use App\Services\HumanResources\AttendanceService;

class AttendanceReportController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['create', 'store', 'edit', 'update', 'destroy']]);
	}

	public function create(): View
	{
		return view('humanresources.hrdept.attendance.attendancereport.create');
	}

	public function store(Request $request): View
	{
		$request->validate(
			[
				'from' => 'required|date',
				'to' => 'required|date',
				'staff_id' => 'required',
			],
			[],
			[
				'from' => 'Begin Date',
				'to' => 'End Date',
				'staff_id' => 'Staff',
			]
		);

		$sa = (new AttendanceService())->reportData($request);

		return view('humanresources.hrdept.attendance.attendancereport.store', ['sa' => $sa]);
	}

}
