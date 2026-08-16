@extends('layouts.app')

@section('content')
<?php

use \Carbon\Carbon;


?>

<div class="page-humanresources-hrdept-attendance-attendancedailyreport-index container">
	@include('humanresources.hrdept.navhr')
	<h4>Attendance Daily Report</h4>

  <form method="POST" action="{{ route('attendancedailyreport.index') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
    @csrf

	<div class="row g-3 mb-3">
		<div class="col-auto" style="position: relative;">
			<input type="text" name="date" value="{{ $selected_date }}" id="date" class="form-control form-control-sm col-auto @error('date') is-invalid @enderror">
		</div>
		<div class="col-auto">
			<button type="submit" class="form-control form-control-sm btn btn-sm btn-outline-secondary">SEARCH</button>
		</div>
	</div>

	</form>

	@if (!empty($dailyreport_absent)|| !empty($lateRows)|| !empty($dailyreport_outstation))
	<div class="row g-3 mb-3">
		<table class="table table-hover table-sm align-middle">

			<!-- ABSENT -->
			@if (!empty($dailyreport_absent))
			<?php $no = 1; ?>
			<tr class="top-row">
				<td colspan="11">
					<b>ABSENT</b>
				</td>
			</tr>
			<tr class="top-row">
				<td class="text-center" style="width: 30px;">
					NO
				</td>
				<td class="text-center" style="width: 75px;">
					DATE
				</td>
				<td class="text-center" style="width: 90px;">
					STATUS
				</td>
				<td class="text-center" style="max-width: 60px;">
					LOCATION
				</td>
				<td class="text-center" style="max-width: 70px;">
					DEPARTMENT
				</td>
				<td class="text-center" style="width: 55px;">
					GROUP
				</td>
				<td class="text-center" style="width: 55px;">
					ID
				</td>
				<td class="text-center" style="max-width: 120px;">
					NAME
				</td>
				<td colspan="2" class="text-center" style="max-width: 100%;">
					REASON / REMARK
				</td>
				<td class="text-center" style="width: 90px;">
					LEAVE ID
				</td>
			</tr>

			@foreach ($dailyreport_absent as $absent)

			<tr>
				<td class="text-center">
					{{ $no++ }}
				</td>
				<td class="text-center">
					{{ $absent->attend_date }}
				</td>
				<td class="text-center" title="{{ $absent->status }}">
					{{ $absent->status }}
				</td>
				<td class="text-truncate text-center" style="max-width: 60px;" title="{{ $absent->code }}">
					{{ $absent->code }}
				</td>
				<td class="text-truncate" style="max-width: 70px;" title="{{ $absent->department }}">
					{{ $absent->department }}
				</td>
				<td class="text-center">
					{{ $absent->group }}
				</td>
				<td class="text-center">
					{{ $absent->username }}
				</td>
				<td class="text-truncate" style="max-width: 120px;" title="{{ $absent->name }}">
					{{ $absent->name }}
				</td>
				<td colspan="2" class="text-truncate" style="max-width: 100%;" title="{{ $absent->remark }}">
					{{ $absent->remark }}
				</td>
				<td class="text-center">
					@if ($absent->leave_number != NULL)
					<a href="{{ route('leave.show', $absent->leave_record_id) }}" target="_blank">
						{{ $absent->leave_number }}
					</a>
					@endif
				</td>
			</tr>
			@endforeach
			@endif


			<!-- LATE -->
			@if ($lateRows->isNotEmpty())
			<?php $no = 1; ?>
			<tr class="top-row">
				<td colspan="11">
					<b>LATE</b>
				</td>
			</tr>
			<tr class="top-row">
				<td class="text-center" style="width: 30px;">
					NO
				</td>
				<td class="text-center" style="width: 75px;">
					DATE
				</td>
				<td class="text-center" style="width: 90px;">
					STATUS
				</td>
				<td class="text-center" style="max-width: 60px;">
					LOCATION
				</td>
				<td class="text-center" style="max-width: 70px;">
					DEPARTMENT
				</td>
				<td class="text-center" style="width: 55px;">
					GROUP
				</td>
				<td class="text-center" style="width: 55px;">
					ID
				</td>
				<td class="text-center" style="max-width: 120px;">
					NAME
				</td>
				<td class="text-center" style="max-width: 100%;">
					REASON / REMARK
				</td>
				<td class="text-center" style="width: 90px;">
					IN
				</td>
				<td class="text-center" style="width: 90px;">
					LEAVE ID
				</td>
			</tr>

			@foreach ($lateRows as $late)

			<tr>
				<td class="text-center">
					{{ $no++ }}
				</td>
				<td class="text-center">
					{{ $late->attend_date }}
				</td>
				<td class="text-center" title="LATE">
					LATE
				</td>
				<td class="text-truncate text-center" style="max-width: 60px;" title="{{ $late->code }}">
					{{ $late->code }}
				</td>
				<td class="text-truncate" style="max-width: 70px;" title="{{ $late->department }}">
					{{ $late->department }}
				</td>
				<td class="text-center">
					{{ $late->group }}
				</td>
				<td class="text-center">
					{{ $late->username }}
				</td>
				<td class="text-truncate" style="max-width: 120px;" title="{{ $late->name }}">
					{{ $late->name }}
				</td>
				<td class="text-truncate" style="max-width: 100%;" title="{{ $late->remark }}">
					{{ $late->remark }}
				</td>
				<td class="text-center">
					<span class="text-danger">{{ $late->in }}</span>
				</td>
				<td class="text-center">
					@if ($late->leave_number != NULL)
					<a href="{{ route('leave.show', $late->leave_record_id) }}" target="_blank">
						{{ $late->leave_number }}
					</a>
					@endif
				</td>
			</tr>
@endforeach
			@endif


			<!-- OUTSTATION -->
			@if (!empty($dailyreport_outstation))
			<?php $no = 1; ?>
			<tr class="top-row">
				<td colspan="11">
					<b>OUTSTATION</b>
				</td>
			</tr>
			<tr class="top-row">
				<td class="text-center" style="width: 30px;">
					NO
				</td>
				<td class="text-center" style="width: 75px;">
					DATE
				</td>
				<td class="text-center" style="width: 90px;">
					STATUS
				</td>
				<td class="text-center" style="max-width: 60px;">
					LOCATION
				</td>
				<td class="text-center" style="max-width: 70px;">
					DEPARTMENT
				</td>
				<td class="text-center" style="width: 55px;">
					GROUP
				</td>
				<td class="text-center" style="width: 55px;">
					ID
				</td>
				<td class="text-center" style="max-width: 120px;">
					NAME
				</td>
				<td colspan="2" class="text-center" style="max-width: 100%;">
					REASON / REMARK
				</td>
				<td class="text-center" style="width: 90px;">
					LEAVE ID
				</td>
			</tr>

			@foreach ($dailyreport_outstation as $outstation)

			<tr>
				<td class="text-center">
					{{ $no++ }}
				</td>
				<td class="text-center">
					{{ $outstation->attend_date }}
				</td>
				<td class="text-center" title="{{ $outstation->status }}">
					{{ $outstation->status }}
				</td>
				<td class="text-truncate text-center" style="max-width: 60px;" title="{{ $outstation->code }}">
					{{ $outstation->code }}
				</td>
				<td class="text-truncate" style="max-width: 70px;" title="{{ $outstation->department }}">
					{{ $outstation->department }}
				</td>
				<td class="text-center">
					{{ $outstation->group }}
				</td>
				<td class="text-center">
					{{ $outstation->username }}
				</td>
				<td class="text-truncate" style="max-width: 120px;" title="{{ $outstation->name }}">
					{{ $outstation->name }}
				</td>
				<td colspan="2" class="text-truncate" style="max-width: 100%;" title="{{ $outstation->remark }}">
					{{ $outstation->remark }}
				</td>
				<td class="text-center">
					{{ $outstation->contact }}
				</td>
			</tr>
@endforeach
			@endif

		</table>
	</div>
	@endif

	<form method="GET" action="{{ route('attendancedailyreport.print') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
	<div class="row">
		<div class="text-center">
			<input type="hidden" name="date" id="date" value="{{ $selected_date }}">

			<input type="submit" class="btn btn-sm btn-outline-secondary" value="PRINT" target="_blank">
		</div>
	</div>
	</form>
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
