<?php

namespace App\Services\HumanResources;

use App\Models\Staff;
use App\Models\HumanResources\AppraisalPivot;
use App\Models\HumanResources\HRAppraisalMark;
use App\Models\HumanResources\OptAppraisalCategories;
use Illuminate\Support\Facades\DB;

/**
 * Appraisal domain queries — extracted from the appraisal blade files so that
 * business logic lives in the service layer, not in the views (M2 refactor).
 */
class AppraisalService
{
	/**
	 * Options for the appraisal apoint index screen (staff / evaluator lists).
	 */  public function apointIndexData(): array
  {
    $newest_year = AppraisalPivot::orderBy('year', 'desc')->first();

    $staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
			->leftjoin('option_appraisal_categories', 'staffs.appraisal_category_id', '=', 'option_appraisal_categories.id')
			->select('logins.username', 'staffs.name', 'staffs.id', 'staffs.appraisal_category_id', 'option_appraisal_categories.category')
			->where('staffs.active', 1)
			->where('logins.active', 1)
			->orderBy('logins.username', 'ASC')
			->get();

		$appraisal_category = OptAppraisalCategories::orderBy('category', 'ASC')
			->pluck('category', 'id')
			->toArray();

		$evaluator = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
			->select(DB::raw('CONCAT(username, " - ", name) AS display_name'), 'staffs.id')
			->where('staffs.active', 1)
			->where('logins.active', 1)
			->orderBy('logins.username', 'ASC')
			->pluck('display_name', 'id')
			->toArray();

		$evaluatees = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
			->join('pivot_staff_pivotdepts', 'staffs.id', '=', 'pivot_staff_pivotdepts.staff_id')
			->join('pivot_dept_cate_branches', 'pivot_staff_pivotdepts.pivot_dept_id', '=', 'pivot_dept_cate_branches.id')
			->select('logins.username', 'staffs.*', 'pivot_dept_cate_branches.department')
			->where('staffs.active', 1)
			->where('logins.active', 1)
			->where('pivot_staff_pivotdepts.main', 1)
			->orderBy('pivot_dept_cate_branches.department', 'ASC')
			->orderBy('logins.username', 'ASC')
			->get();    // evaluators already apointed per evaluatee, keyed by evaluatee id (used by the apoint list)
    $markersByEvaluatee = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
      ->join('pivot_apoint_appraisals', 'staffs.id', '=', 'evaluator_id')
      ->select('logins.username', 'staffs.name', 'pivot_apoint_appraisals.id', 'pivot_apoint_appraisals.evaluatee_id')
      ->where('staffs.active', 1)
      ->where('logins.active', 1)
      ->whereNull('pivot_apoint_appraisals.deleted_at')
      ->where('pivot_apoint_appraisals.year', $newest_year?->year)
      ->orderBy('logins.username', 'ASC')
      ->get()
      ->groupBy('evaluatee_id');

    return compact('newest_year', 'staffs', 'appraisal_category', 'evaluator', 'evaluatees', 'markersByEvaluatee');
  }

	/**
	 * Distinct published form versions, grouped by appraisal category id.
	 * Used by the form index screen (per-category version list).
	 */
	public function formVersionsByCategory(): \Illuminate\Support\Collection
	{
		return DB::table('pivot_category_appraisals')
			->whereNotNull('version')
			->whereNull('deleted_at')
			->orderBy('version', 'ASC')
			->get()
			->groupBy('category_id')
			->map(fn ($rows) => $rows->unique('version')->values());
	}

	/**
	 * Data for showing / editing / printing a single appraisal form (pivot id).
	 */
	public function formData(int $id): array
	{
		$pivotappraisal = DB::table('pivot_category_appraisals')
			->where('id', $id)
			->first();

		$category = OptAppraisalCategories::where('id', $pivotappraisal->category_id)->first();

		$appraisals = DB::table('pivot_category_appraisals')
			->where('category_id', $pivotappraisal->category_id)
			->where('version', $pivotappraisal->version)
			->orderBy('sort', 'ASC')
			->orderBy('id', 'ASC')
			->get();

		return compact('pivotappraisal', 'category', 'appraisals');
	}

	/**
	 * Appraisals assigned to the logged-in evaluator (mark index screen).
	 */
	public function markIndexData(): array
	{
		$user = auth()->user()->belongstostaff->id;

		$appraisals = DB::table('pivot_apoint_appraisals')
			->join('logins', 'logins.staff_id', '=', 'pivot_apoint_appraisals.evaluatee_id')
			->join('staffs', 'staffs.id', '=', 'pivot_apoint_appraisals.evaluatee_id')
			->where('pivot_apoint_appraisals.evaluator_id', $user)
			->where('logins.active', 1)
			->whereNull('pivot_apoint_appraisals.deleted_at')
			->select('pivot_apoint_appraisals.id as apointid', 'staffs.name', 'logins.username', 'staffs.appraisal_category_id', 'pivot_apoint_appraisals.finalise_date')
			->orderBy('logins.username', 'ASC')
			->get();

		return compact('appraisals');
	}

	/**
	 * Data for the mark create/show screens for a single apoint appraisal.
	 * Mark lookups are pre-loaded into keyed maps so the view never queries.
	 */
	public function markData(int $id, bool $activeLogin = true): array
	{
		$staff = Staff::join('pivot_apoint_appraisals', 'pivot_apoint_appraisals.evaluatee_id', '=', 'staffs.id')
			->join('logins', 'logins.staff_id', '=', 'staffs.id')
			->where('pivot_apoint_appraisals.id', $id)
			->when($activeLogin, fn ($q) => $q->where('logins.active', 1))
			->select('staffs.id as staffid', 'staffs.appraisal_category_id as catid', 'staffs.*', 'logins.*', 'pivot_apoint_appraisals.*')
			->first();

		$pivotappraisal = DB::table('pivot_category_appraisals')
			->join('option_appraisal_categories', 'option_appraisal_categories.id', '=', 'pivot_category_appraisals.category_id')
			->where('pivot_category_appraisals.category_id', $staff?->catid)
			->orderBy('version', 'DESC')
			->first();

		$appraisals = DB::table('pivot_category_appraisals')
			->where('category_id', $pivotappraisal?->category_id)
			->where('version', $pivotappraisal?->version)
			->orderBy('sort', 'ASC')
			->orderBy('id', 'ASC')
			->get();    $marks = HRAppraisalMark::where('pivot_apoint_id', $id)->get();

    $staff_dept = $staff
      ? $staff->belongstomanydepartment()?->where('main', 1)->first()?->department
      : null;

    return [
      'staff' => $staff,
      'staff_dept' => $staff_dept,
      'pivotappraisal' => $pivotappraisal,
      'appraisals' => $appraisals,
      'markByQuestion' => $marks->keyBy('question_id'),
      'markBySectionSub' => $marks->keyBy('section_sub_id'),
      'markByMainQuestion' => $marks->keyBy('main_question_id'),
    ];
  }
}
