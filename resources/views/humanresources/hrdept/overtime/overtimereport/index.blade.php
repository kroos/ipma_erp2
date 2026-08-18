@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-overtime-overtimereport-index container">
  @include('humanresources.hrdept.navhr')
  <h4>Overtime Report</h4>

  <form method="POST" action="{{ route('overtimereport.index') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
    @csrf

  <div class="row g-3 mb-3">
    <div class="col-auto" style="position: relative;">
      <input type="text" name="date_start" value="{{ old('date_start') }}" id="date_start" class="form-control form-control-sm col-sm-12 @error('date_start') is-invalid @enderror" placeholder="Date Start">
    </div>
    @error('date_start')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="col-auto" style="position: relative;">
      <input type="text" name="date_end" value="{{ old('date_end') }}" id="date_end" class="form-control form-control-sm col-sm-12 @error('date_end') is-invalid @enderror" placeholder="Date End">
    </div>
    @error('date_end')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="col-auto">
      <select name="branch" id="branch" class="form-select form-select-sm branch @error('branch') is-invalid @enderror">
        <option value="">Please choose</option>
        @foreach($locations as $k1 => $v1)
          <option value="{{ $k1 }}" {{ (old('branch') == $k1)?'selected':NULL }}>{{ $v1 }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto">
      <select class="form-select form-select-sm title @error('title') is-invalid @enderror" id="title" name="title">
        <option selected="selected" value=""></option>
        <option value="1st half">1st half</option>
        <option value="2nd half">2nd half</option>
      </select>
    </div>
    <div class="col-auto">
      <select class="form-control month" id="month" name="month">
        <option selected="selected" value=""></option>
        <option value="January">January</option>
        <option value="February">February</option>
        <option value="March">March</option>
        <option value="April">April</option>
        <option value="May">May</option>
        <option value="June">June</option>
        <option value="July">July</option>
        <option value="August">August</option>
        <option value="September">September</option>
        <option value="October">October</option>
        <option value="November">November</option>
        <option value="December">December</option>
      </select>
    </div>
    <div class="col-auto">
      <select class="form-select form-select-sm year @error('year') is-invalid @enderror" id="year" name="year">
        <option selected="selected" value=""></option>		<option value="{{ $report['current_year'] }}">{{ $report['current_year'] }}</option>
        <option value="{{ $report['last_year'] }}">{{ $report['last_year'] }}</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-sm btn-outline-secondary">Submit</button>
    </div>
  </div>

  </form>	  @if ($overtimes != NULL)
  <div class="row g-3 mb-3 text-center">
    <div class="text-center">
      {{ $report['claim_form_title'] }} ({{ $title }} of {{ $month }} {{ $year }})
    </div>
  </div>

  <div class="row g-3 mb-3">
    <table class="table table-hover table-sm align-middle">
      <tr class="top-row">
        <td class="text-center" style="width: 30px;">
          NO
        </td>
        <td class="text-center" style="width: 55px;">
          ID
        </td>
        <td class="text-center" style="max-width: 150px;">
          NAME
        </td>
        <td class="text-center">
          DEPARTMENT
        </td>		@foreach ($report['columns'] as $column)
        <td class="text-center" style="max-width: 48px;">
          {{ $column['label'] }}
        </td>
        @endforeach
        <td class="text-center" style="max-width: 60px;">
          TOTAL<br />HOURS
        </td>
        <td class="text-center" style="max-width: 70px;">
          SIGNATURE
        </td>
      </tr>	      @foreach ($report['rows'] as $index => $overtime)
      <tr>
        <td class="text-truncate text-center" style="width: 30px;">
          {{ $index + 1 }}
        </td>
        <td class="text-truncate text-center" style="width: 55px;" title="{{ $overtime['username'] }}">
          {{ $overtime['username'] }}
        </td>
        <td class="text-truncate" style="max-width: 150px;" title="{{ $overtime['name'] }}">
          {{ $overtime['name'] }}
        </td>
        <td class="text-truncate" style="max-width: 1px;" title="{{ $overtime['department'] }}">
          {{ $overtime['department'] }}
        </td>
        @foreach ($overtime['cells'] as $cell)
        <td class="text-truncate text-center" style="max-width: 48px;{{ $cell['background'] }}">
          {{ $cell['time'] }}
        </td>
        @endforeach
        <td class="text-center" style="max-width: 60px;">
          {{ $overtime['total'] }}
        </td>
        <td style="max-width: 70px;"></td>
      </tr>
      @endforeach

      <tr>
        <td align="right" colspan="{{ $report['total_col'] + 4 }}">
          TOTAL HOURS
        </td>
        <td class="text-center">
          {{ $report['grand_total'] }}
        </td>
        <td></td>
      </tr>
    </table>

    <div class="row">
      <div style="width: 25px; height: 25px; background-color: #d9d9d9;"></div>&nbsp;REMARK
    </div>

    <form method="GET" action="{{ route('overtimereport.print') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
      @csrf
    <div class="row">
      <div class="text-center">
        <input type="hidden" name="date_start" id="date_start" value="{{ $date_start }}">
        <input type="hidden" name="date_end" id="date_end" value="{{ $date_end }}">
        <input type="hidden" name="branch" id="branch" value="{{ $branch }}">
        <input type="hidden" name="title" id="title" value="{{ $title }}">
        <input type="hidden" name="month" id="month" value="{{ $month }}">
        <input type="hidden" name="year" id="year" value="{{ $year }}">

        <input type="submit" class="btn btn-sm btn-outline-secondary" value="PRINT" target="_blank">
      </div>
    </div>
    </form>
  </div>
  @endif

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
