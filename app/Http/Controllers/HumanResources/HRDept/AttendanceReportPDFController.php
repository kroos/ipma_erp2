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

// load pdf
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceReportPDFController extends Controller
{
	function __construct()
	{
		$this->middleware(['auth']);
		$this->middleware('highMgmtAccess:1|2|5,14|31', ['only' => ['store']]);
	}

	public function store(Request $request)
	{
		// heavy reports (big date ranges) need extra room
		ini_set('max_execution_time', 3000);
		ini_set('memory_limit', '1024M');

		$sa = (new AttendanceService())->reportData($request);

		$pdf = PDF::loadView('humanresources.hrdept.attendance.attendancereport.storepdf', ['sa' => $sa]);
		// return $pdf->download('attendance monthly report ' . $request->from . ' - ' . $request->to . '.pdf');
		return $pdf->stream();
	}
}
