@extends('layouts.app')

@section('content')
<div class="col-sm-12 row">
@include('humanresources.hrdept.navhr')
	<div class="row justify-content-center">
		<div class="table-responsive">
			<h2>Attendance Remark Details &nbsp; <a href="{{ route('attendanceremark.index') }}" class="btn btn-sm btn-outline-secondary">Back</a></h2>
			<table class="table table-sm table-hover m-3" id="attendanceremark" style="font: 12px roboto-flex;">
				<tbody>
					<tr>
						<td style="width: 150px;"><strong>Staff</strong></td>
						<td>{{ $attendanceremark->belongstostaff?->name }}</td>
					</tr>
					<tr>
						<td><strong>From</strong></td>
						<td>{{ $attendanceremark->date_from ? \Carbon\Carbon::parse($attendanceremark->date_from)->format('j M Y') : '' }}</td>
					</tr>
					<tr>
						<td><strong>To</strong></td>
						<td>{{ $attendanceremark->date_to ? \Carbon\Carbon::parse($attendanceremark->date_to)->format('j M Y') : '' }}</td>
					</tr>
					<tr>
						<td><strong>Attendance Remarks</strong></td>
						<td>{{ $attendanceremark->attendance_remarks }}</td>
					</tr>
					<tr>
						<td><strong>HR Attendance Remarks</strong></td>
						<td>{{ $attendanceremark->hr_attendance_remarks }}</td>
					</tr>
					<tr>
						<td><strong>Remarks</strong></td>
						<td>{{ $attendanceremark->remarks }}</td>
					</tr>
				</tbody>
			</table>
		</div>
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
};

@endsection
