@extends('layouts.app')

@section('content')

<div class="container">
  <div class="row mt-3">
    <div class="col-md-2">
      <h4>Appraisal </h4>
    </div>
  </div>

  <div class="row">&nbsp;</div>

  <div>
    <table id="staff" class="table table-hover table-sm align-middle" style="font-size:12px">
      <thead>
        <tr>
          <th class="text-center" style="max-width: 60px;">ID</th>
          <th>Name</th>
          <th style="max-width: 150px;"></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($appraisals as $appraisal)

        <tr>
          <td class="text-center">
            {{ $appraisal->username }}
          </td>
          <td data-toggle="tooltip" title="{{ $appraisal->name }}">
            <input type="text" readonly value="{{ $appraisal->name }}" style="border-style:none; outline:none; background-color:transparent; width:95%; height:100%;" />
          </td>
          <!-- IF ERROR : Please Apoint A Form To Every Evaluatees -->
          <td class="text-center">
          @if(is_null($appraisal->finalise_date))
            <a href="{{ route('appraisalmark.create', ['id' => $appraisal->apointid]) }}" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-pencil-square" style="font-size: 15px; color: red;"> PENDING</i>
            </a>
            @else
            <a href="{{ route('appraisalmark.show', ['id' => $appraisal->apointid]) }}" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-pencil-square" style="font-size: 15px; color: green;"> FINALLISE</i>
            </a>
            @endif
          </td>
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
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection