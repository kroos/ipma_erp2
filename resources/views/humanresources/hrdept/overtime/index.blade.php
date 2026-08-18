@extends('layouts.app')

@section('content')
<div class="container row justify-content-center align-items-start">
@include('humanresources.hrdept.navhr')
	<h2>Staffs Overtime&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('overtime.create') }}"><i class="fa-solid fa-person-circle-plus fa-beat"></i> Add Staff Overtime</a></h2>
	<div class="d-flex justify-content-center">
		{!! $sa->links() !!}
	</div>
	<div class="table-responsive">
		<table id="overtime" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th rowspan="2">ID</th>
					<th rowspan="2">Name</th>
					<th rowspan="2">Date</th>
					<th colspan="2" rowspan="1">Overtime</th>
					<th rowspan="2">Duration</th>
					<th rowspan="2">Assign By</th>
					<th rowspan="2">Remarks</th>
					<th rowspan="2">#</th>
				</tr>
				<tr>
					<th>Start Time</th>
					<th>End Time</th>
				</tr>
			</thead>
			<tbody>

				@foreach($overtime as $key)
					<tr>
						<td>{{ $key->username }}</td>
						<td>{{ $key->belongstostaff?->name }}</td>
						<td>{{ $key->ot_date_fmt }}</td>
						<td>{{ $key->start_fmt }}</td>
						<td>{{ $key->end_fmt }}</td>
							<td>{{ $key->belongstoovertimerange?->total_time }}</td>
							<td>{{ $key->belongstoassignstaff?->name }}</td>
							<td data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-html="true" data-bs-title="{{ ($key->remark)??' ' }}">{{ Str::limit($key->remark, 8, ' >') }}</td>
							<td>
								<a href="{{ route('overtime.edit', $key->id) }}" class="btn btn-sm btn-outline-secondary">
									<i class="bi bi-pencil-square" style="font-size: 15px;"></i>
								</a>
								<button type="button" class="btn btn-sm btn-outline-secondary delete_overtime" data-id="{{ $key->id }}" >
									<i class="fa-regular fa-trash-can"></i>
								</button>
							</td>
						</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="d-flex justify-content-center">

	</div>
</div>
@endsection

@section('js')
window.data = {
	route: {
	},
	url: {
		overtime: '{{ url('overtime') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection

@section('nonjquery')
@endsection
