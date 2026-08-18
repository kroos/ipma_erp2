@extends('layouts.app')

@section('content')
<div class="col-sm-12 row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Outstation Attendance List&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('hroutstationattendance.create') }}"><i class="fa-solid fa-person-digging fa-beat"></i> Add Outstation Attendance</a></h4>

	@if($hroa->count())
	<form method="POST" action="{{ route('confirmoutstationattendance') }}" accept-charset="UTF-8" id="form" autocomplete="off" class="" enctype="multipart/form-data">
		@csrf
	<div class="table-responsive">
		<table id="outstation" class="table table-hover table-sm align-middle" style="font-size:12px">
				<thead>
					<tr>
						<th>
							<input type="checkbox" name="name" value="" id="checkAll" class="form-check-input">
						</th>
						<th>ID</th>
						<th>Staff</th>
						<th>Location</th>
						<th>Date</th>
						<th>In</th>
						{{-- <th>Detected Region In</th>
						<th>Detected City In</th> --}}
						<th>Out</th>
						{{-- <th>Detected Region Out</th>
						<th>Detected City Out</th> --}}
						<th>Confirm Attendance</th>
						<th>Date Confirm Attendance</th>
						<th>Remarks</th>
						<th>#</th>
					</tr>
				</thead>
				<tbody>
				@foreach($hroa as $k => $v)
					<tr>
						<td>
							@if(!$v->confirm)
								<input type="checkbox" name="id[]" value="{{ $v->id }}" class="form-check-input @error('id.*') is-invalid @enderror" {{ (old('id.*') == $v->id)?'checked':NULL }}>
							@endif
						</td>
						<td>{{ $usernames[$v->staff_id] ?? '' }}</td>
						<td>{{ $staffNames[$v->staff_id] ?? '' }}</td>
						<td @if($v->outstation_id) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $outstationCustomers[$v->outstation_id] ?? '' }}" @endif>
							{{ Str::limit($outstationCustomers[$v->outstation_id] ?? '', 7, ' >>') }}
						</td>
						<td>{{ $v->date_attend_fmt }}</td>
						<td>{{ $v->in_fmt }}</td>
						{{-- <td>{{ $v->in_regionName }}</td>
						<td>{{ $v->in_cityName }}</td> --}}
						<td>{{ $v->out_fmt }}</td>
						{{-- <td>{{ $v->out_regionName }}</td>
						<td>{{ $v->out_cityName }}</td> --}}
						<td>{{ ($v->confirm)?'Sended':'Not Sended' }}</td>
						<td>{{ $v->date_confirm_fmt }}</td>
						<td @if($v->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $v->remarks }}" @endif>
							{{ Str::limit($v->remarks, 8, ' >') }}
						</td>
						<td>
							<a href="{{ route('hroutstationattendance.edit', $v->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							<button type="button" id="out" class="btn btn-sm btn-outline-secondary text-danger delete_button" data-id="{{ $v->id }}"><i class="fa-regular fa-trash-can"></i></button>
						</td>
					</tr>
				@endforeach
				</tbody>
		</table>
	</div>
	<div class="offset-sm-6 col-sm-auto mt-3">
		<button type="submit" class="btn btn-sm btn-outline-secondary">Send to Main Attendance</button>
	</div>
	</form>
	@endif
</div>
@endsection

@section('js')
window.data = {
	route: {
		destroy: '{{ route('hroutstationattendance.destroy', ['hroutstationattendance' => ':id']) }}',
	},
	url: {
		outstation: '{{ url('outstation') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
