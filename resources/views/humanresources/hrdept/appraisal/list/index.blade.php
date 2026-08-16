@extends('layouts.app')


@section('content')

<?php

use \App\Models\Staff;
use \App\Models\HumanResources\OptAppraisalCategories;
use \App\Models\HumanResources\AppraisalPivot;

$newest_year = AppraisalPivot::orderBy('year', 'desc')->first();

$staffs = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
  ->select('logins.username', 'staffs.name', 'staffs.id')
  ->where('staffs.active', 1)
  ->where('logins.active', 1)
  ->orderBy('logins.username', 'ASC')
  ->get();
?>

<div class="page-humanresources-hrdept-appraisal-list-index container">
  @include('humanresources.hrdept.navhr')

  <div class="row mt-3">
    <div class="col-md-2">
      <h4>Appraisal List</h4>
    </div>
    <div class="col-md-10">

    </div>
  </div>

  <div class="row">&nbsp;</div>

  <div>
    <table id="staff" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th class="text-center" style="max-width: 30px;">ID</th>
          <th class="text-center">Name</th>
          <th class="text-center" style="max-width: 80px;">Location</th>
          <th class="text-center" style="max-width: 100px;">Department</th>
          <th class="text-center" style="max-width: 150px;">Evaluator1</th>
          <th class="text-center" style="max-width: 150px;">Evaluator2</th>
          <th class="text-center" style="max-width: 150px;">Evaluator3</th>
          <th class="text-center" style="max-width: 150px;">Evaluator4</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($staffs as $staff)

        <?php
        $markers = Staff::join('logins', 'staffs.id', '=', 'logins.staff_id')
          ->join('pivot_apoint_appraisals', 'staffs.id', '=', 'evaluator_id')
          ->select('logins.username', 'staffs.name', 'pivot_apoint_appraisals.id', 'pivot_apoint_appraisals.appraisal_category_id', 'pivot_apoint_appraisals.finalise_date')
          ->where('staffs.active', 1)
          ->where('logins.active', 1)
          ->whereNull('pivot_apoint_appraisals.deleted_at')
          ->where('pivot_apoint_appraisals.evaluatee_id', $staff->id)
          ->where('pivot_apoint_appraisals.year', $newest_year->year)
          //->whereNull('pivot_apoint_appraisals.finalise_date')
          ->orderBy('logins.username', 'ASC')
          ->get();
        ?>

        <tr>
          <td class="text-center">
            {{ $staff->username }}
          </td>
          <td data-toggle="tooltip" title="{{ $staff->name }}">
            <input type="text" readonly value="{{ $staff->name }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          <td class="text-center">

          </td>
          <td class="text-center">

          </td>

          @foreach ($markers as $marker)
          <td data-toggle="tooltip" title="{{ $marker->name }}">
            @if(is_null($marker->finalise_date))
            <input type="text" readonly value="{{ $marker->name }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%; color:red;" />
            @else
            <input type="text" readonly value="{{ $marker->name }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%; color:green;" />
            @endif
          </td>
          @endforeach

          @for ($a=count($markers); $a<'4'; $a++)
            <td>
            </td>
            @endfor
        </tr>

        @endforeach
      </tbody>
    </table>
  </div>

</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
		appraisallistUpdate: '{{ url('appraisallist/update') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection