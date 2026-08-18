@extends('layouts.app')

@section('content')
<div class="container table-responsive row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<div class="row g-3">
		<h4>Attendance By Staff</h4>
		@if($sa)
		<form method="GET" action="{{ route('attendancereportpdf.store') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="form-horizontal" enctype="multipart/form-data">
			@csrf
			<div class="col-sm-2">
				<input type="hidden" name="from" value="{{ request('from') }}">
				<input type="hidden" name="to" value="{{ request('to') }}">
				@foreach($sa as $key)
				<input type="hidden" name="staff_id[]" value="{{ $key['staff_id'] }}">
				@endforeach
				<input type="submit" class="form-control form-control-sm btn btn-sm btn-outline-secondary" value="Print PDF" target="_blank">
			</div>
		</form>
		@endif
		<p>&nbsp;</p>
		@if($sa)
		@foreach($sa as $v)
		<div class="d-print-table">
			<h5>
				{{ $v['username'] }} {{ $v['name'] }}<br />
				{{ $v['dept'] }}<br />
				{{ $v['group'] }}
			</h5>
			<table id="attendancestaff_" class="table table-hover table-sm table-bordered align-middle" style="font-size:12px">
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">Name</th>
						<th scope="col">Type</th>
						<th scope="col">Cause</th>
						<th scope="col">Leave</th>
						<th scope="col">Date</th>
						<th scope="col">In</th>
						<th scope="col">Break</th>
						<th scope="col">Resume</th>
						<th scope="col">Out</th>
						<th scope="col">Duration</th>
						<th scope="col">Overtime</th>
						<th scope="col">Outstation</th>
						<th scope="col">Remarks</th>
						<th scope="col">Exception</th>
					</tr>
				</thead>
				<tbody>
				@foreach($v['rows'] as $v1)
					<tr class="{{ $v1['row_class'] }}">
						<td>{{ $v['username'] }}</td>
						<td>{{ $v['name'] }}</td>
						<td>{{ $v1['dayt'] }}</td>
						<td>{!! $v1['leave_html'] !!}</td>
						<td>{!! $v1['lea_html'] !!}</td>
						<td>{{ $v1['date'] }}</td>
						<td><span class="{{ $v1['in_class'] }}">{{ $v1['in'] }}</span></td>
						<td><span class="{{ $v1['break_class'] }}">{{ $v1['break'] }}</span></td>
						<td><span class="{{ $v1['resume_class'] }}">{{ $v1['resume'] }}</span></td>
						<td><span class="{{ $v1['out_class'] }}">{{ $v1['out'] }}</span></td>
						<td>{{ $v1['duration'] }}</td>
						<td>{{ $v1['overtime'] }}</td>
						<td>{{ $v1['outstation'] }}</td>
						<td class="w-25">{!! $v1['remarks'] !!}</td>
						<td>{{ $v1['exception'] }}</td>
					</tr>
				@endforeach
				</tbody>
				<tfoot>
					<tr>
						<td colspan="10" rowspan="1"></td>
						<td><strong class="text-success">{{ $v['duration_total'] }}</strong></td>
						<td><strong class="text-success">{{ $v['overtime_total'] }}</strong></td>
						<td colspan="2" rowspan="1"></td>
					</tr>
				</tfoot>
			</table>
		</div>
		@endforeach
		@endif
	</div>
</div>
@endsection

@section('js')
@endsection
