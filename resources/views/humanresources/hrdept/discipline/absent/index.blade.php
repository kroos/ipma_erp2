@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Staff Absent Record</h4>
	<div class="col-sm-12 table-responsive row m-3">
		<table class="table table-hover table-sm" id="active" style="font-size:12px">
		@foreach($absents as $tp)
			<thead>
				<tr>
					<th class="text-primary" colspan="7">Staff Absent On Year {{ $tp['ayear'] }}</th>
				</tr>
				@foreach($tp['staffs'] as $value)
					<tr>
						<th class="text-success" colspan="7">Absent Staff on {{ $tp['ayear'] }} For {{ $value['username'] }} {{ $value['name'] }}</th>
					</tr>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Date</th>
						<th>Absent</th>
						<th>Leave</th>
						<th>Outstation</th>
						<th>Remarks</th>
					</tr>
				</thead>
				@foreach($value['rows'] as $t)
				<tbody>
					<tr>
						<td>{{ $value['username'] }}</td>
						<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ $t->name }}">
							{{ Str::words($t->name, 3, ' >') }}
						</td>
						<td>{{ $t->date_fmt }}</td>
						<td>
							{{ $t->leave_short }}
						</td>
						<td>
							@if($t->leave_id)
								<a href="{{ route('hrleave.show', $t->leave_id) }}" target="_blank">
									{{ $t->leave_ref }}
								</a>
							@endif
						</td>
						<td>
							@if($t->outstation_id)
								{{ $t->belongstocustomer?->customer }}
							@endif
						</td>
						<td @if($t->remarks || $t->hr_remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $t->remarks }} {{ $t->hr_remarks }}" @endif>
							{{ Str::limit($t->remarks, 8, ' >') }}
							<br />
							<span class="text-danger">
								{{ Str::limit($t->hr_remarks, 8, ' >') }}
							</span>
						</td>
					</tr>
				</tbody>
				@endforeach
				<tfoot>
					<tr>
						<th colspan="2"></th>
						<th>Total</th>
						<th>{{ $value['dur'] }} day/s</th>
						<th colspan="3"></th>
					</tr>
				</tfoot>
				@endforeach
			@endforeach
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
};
@endsection
