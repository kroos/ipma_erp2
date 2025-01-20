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
// use App\Http\Requests\HumanResources\Attendance\AttendanceRequestUpdate;

// load facade
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// load models
use App\Models\Staff;
use App\Models\HumanResources\DepartmentPivot;

// load paginator
use Illuminate\Pagination\Paginator;

// load cursor pagination
use Illuminate\Pagination\CursorPaginator;

// load array helper
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

// load Carbon
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use Session;

class AppraisalApointController extends Controller
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
  public function index(): View
  {
    $departments = DepartmentPivot::all();

    return view('humanresources.hrdept.appraisal.apoint.index', ['departments' => $departments]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create(): View
  {
    // $department = DepartmentPivot::where('id', $id)->first();
    // return view('humanresources.hrdept.appraisal.form.create', ['department' => $department]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request): RedirectResponse
  {
    $now = Carbon::now();
    $year = $now->format('Y');

    // foreach ($request->evaluetee_id as $evaluateeid) {
    //   $evaluateeid = Staff::find($evaluateeid);
    //   $evaluateeid->belongstomanyevaluator()->attach($request->evaluator_id, ['year' => $year]);
    // }

    foreach ($request->evaluetee_id as $evaluateeid) {
      $evaluatee = Staff::find($evaluateeid);
      $exists = $evaluatee->belongstomanyevaluator()->where('evaluator_id', $request->evaluator_id)->where('year', $year)->exists();

      if (!$exists) { // Use $exists here
        $evaluatee->belongstomanyevaluator()->attach($request->evaluator_id, ['year' => $year]);
      } else {
        DB::table('pivot_apoint_appraisals')
        ->where('evaluator_id', $request->evaluator_id)
        ->where('evaluatee_id', $evaluateeid)
        ->where('year', $year)
          ->update(['deleted_at' => NULL]);
      }
    }

    Session::flash('flash_message', 'Successfully Apoint.');
    return redirect()->route('appraisalapoint.index');
  }

  /**
   * Display the specified resource.
   */
  public function show(): View
  {
    // return view('humanresources.hrdept.appraisal.form.show', ['id' => $appraisalform]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(): View
  {
    // return view('humanresources.hrdept.appraisal.form.edit', ['id' => $appraisalform]);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request): JsonResponse
  {
    DB::table('staffs')
      ->where('id', $request->id)
      ->update(['appraisal_category_id' => $request->category_id]);

    return response()->json([
      'message' => 'Successful Updated',
      'status' => 'success'
    ]);
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Request $request): JsonResponse
  {
    $datetime = Carbon::now();

    DB::table('pivot_apoint_appraisals')
      ->where('id', $request->id)
      ->update(['deleted_at' => $datetime]);

    return response()->json([
      'message' => 'Successful Deleted',
      'status' => 'success'
    ]);
  }
}
