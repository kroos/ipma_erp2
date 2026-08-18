<?php

namespace App\Http\Controllers\HumanResources\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// for controller output
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

// load models
use App\Models\Login;
use App\Models\Staff;

// load services
use App\Services\HumanResources\StaffProfileService;

// load validation
use App\Http\Requests\HumanResources\Profile\ProfileRequestUpdate;

use Session;

class ProfileController extends Controller
{

	function __construct()
	{
		$this->middleware('auth');
		$this->middleware('profileaccess', ['only' => ['show', 'edit', 'update']]);
	}

	/**
	 * Display a listing of the resource.
	 */
	public function index(): View
	{
		return view('humanresources.profile.index');
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
	public function store(Request $request): View
	{
		//
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Request $request, Staff $profile): View
	{
		$current_time = now();
		$current_year = $current_time->format('Y');
		$current_month = $current_time->format('m');

		if ($request->year != NULL && $request->month != NULL) {
			$year = $request->year;
			$month = $request->month;
		} else {
			$year = $current_year;
			$month = $current_month;
		}

		$dept = $profile->belongstomanydepartment()->wherePivot('main', 1)->first();

		$service = new StaffProfileService();

		return view('humanresources.profile.show', [
			'profile' => $profile,
			'dept' => $dept,
			'year' => $year,
			'month' => $month,
			'attendanceRows' => $service->attendanceRows($profile, $year, $month),
		] + $service->leaveEntitlements($profile)
			+ $service->familyAndAuth($profile)
			+ $service->attendanceYears($profile)
			+ $service->leaveTables($profile)
			+ $service->replacementTable($profile)
			+ $service->unpaidLeaves($profile)
			+ $service->leaveTypeMap()
			+ $service->profileCard($profile, 'd F Y'));
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(Staff $profile): View
	{
		return view('humanresources.profile.edit', compact('profile'));
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(ProfileRequestUpdate $request, Staff $profile): RedirectResponse
	{

		$login = Login::find($request->input('login_id'));

		if ($login) {
			// Update the password field
			$login->password = $request->input('password');
			$login->save(); // Save the changes to the database
		}

		Session::flash('message', 'Password successfully updated!');
		return Redirect::route('profile.show', $profile);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy()
	{
		//
	}
}
