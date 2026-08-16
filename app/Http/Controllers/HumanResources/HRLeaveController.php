<?php
namespace App\Http\Controllers\HumanResources;
use App\Http\Controllers\Controller;

// for controller output
use Illuminate\Http\{
	JsonResponse,
	RedirectResponse,
	// Response
};
// use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// models
use App\Models\{
	HumanResources\HRLeave,
};

// load request
use Illuminate\Http\Request;
use App\Http\Requests\HumanResources\{
	StoreHRLeaveRequest,
	UpdateHRLeaveRequest,
};
// use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Rule;

// resource
// use App\Resources\HumanResources\HRLeaveResource;

// service
// use App\Services\HumanResources\HRLeaveService;

// policy
// use App\Policies\HumanResources\HRLeavePolicy;

// load db facade
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\DB;

// load batch and queue
// use Illuminate\Bus\Batch;
// use Illuminate\Support\Facades\Bus;

// load email & notification
// use Illuminate\Support\Facades\{
// 	Mail, Notification
// };

// load helper
use Illuminate\Support\{
	Arr, Str, Collection, Facades\Storage
};

// load Carbon library
use \Carbon\{
	Carbon, CarbonPeriod, CarbonInterval
};

// use Session;
// use Throwable;
// use Exception;
// use Log;

// load pdf
// use Barryvdh\DomPDF\Facade\Pdf;

class HRLeaveController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('h-r-leave.index');
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create(): View
	{
		return view('h-r-leave.create');
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(StoreHRLeaveRequest $request): RedirectResponse
	{
		HRLeave::create(
			$request->validated()
		);

		return redirect()->route('h-r-leave.index')->with('success', 'Data Added');
	}

	/**
	 * Display the specified resource.
	 */
	public function show(HRLeave $hRLeave): View
	{
		return view('h-r-leave.show', ['var' => $hRLeave]);
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(HRLeave $hRLeave): View
	{
		return view('h-r-leave.edit', ['var' => $hRLeave]);
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(
		UpdateHRLeaveRequest $request,
		HRLeave $hRLeave
	): RedirectResponse
	{
		$hRLeave->update(
			$request->validated()
		);

		return redirect()->route('h-r-leave.index')->with('success', 'Data Updated');
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(HRLeave $hRLeave): JsonResponse
	{
		$hRLeave->delete();
		return response()->json([
			'status' => 'success',
			'message' => 'Data Deleted',
		]);
	}
}
