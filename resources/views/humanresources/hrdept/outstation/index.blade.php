@extends('layouts.app')

@section('content')
<div class="container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Outstation List&nbsp;<a class="btn btn-sm btn-outline-secondary" href="{{ route('outstation.create') }}"><i class="fa-solid fa-person-digging fa-beat"></i> Add Outstation</a></h4>
	<div class="table-responsive m-3">
		<table class="table table-hover table-sm" id="nowoutstation" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center" colspan="7">{{ $nowyear }}</th>
				</tr>
				<tr>
					<th>ID Staff</th>
					<th>Staff</th>
					<th>Location</th>
					<th>From</th>
					<th>To</th>
					<!-- <th>Duration</th> -->
					<th>Remarks</th>
					<th>#</th>
				</tr>
			</thead>
			<tbody>
				@foreach($outstationsNow as $key => $outstation)
					<tr>
						<td>{{ $usernames[$outstation->staff_id] ?? '' }}</td>
						<td>{{ $staffNames[$outstation->staff_id] ?? '' }}</td>
						<td>{{ $outstation->belongstocustomer?->customer }}</td>
						<td>{{ $outstation->date_from_fmt }}</td>
						<td>{{ $outstation->date_to_fmt }}</td>
						<!-- duration day/s (decorated server-side in OutstationController::index) -->
						<td @if($outstation->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $outstation->remarks }}" @endif>
							{{ Str::limit($outstation->remarks, 7, ' >') }}
						</td>
						<td>
							<a href="{{ route('outstation.edit', $outstation->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							<button type="button" id="out" class="btn btn-sm btn-outline-secondary text-danger delete_button" data-id="{{ $outstation->id }}"><i class="fa-regular fa-trash-can"></i></button>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
	<div class="table-responsive m-3">
		<table class="table table-hover table-sm" id="lastoutstation" style="font-size:12px;">
			<thead>
				<tr>
					<th class="text-center" colspan="7">{{ $lastyear }}</th>
				</tr>
				<tr>
					<th>ID Staff</th>
					<th>Staff</th>
					<th>Location</th>
					<th>From</th>
					<th>To</th>
					<!-- <th>Duration</th> -->
					<th>Remarks</th>
					<th>#</th>
				</tr>
			</thead>
			<tbody>
				@foreach($outstationsLast as $key => $outstation)
					<tr>
						<td>{{ $usernames[$outstation->staff_id] ?? '' }}</td>
						<td>{{ $staffNames[$outstation->staff_id] ?? '' }}</td>
						<td>{{ $outstation->belongstocustomer?->customer }}</td>
						<td>{{ $outstation->date_from_fmt }}</td>
						<td>{{ $outstation->date_to_fmt }}</td>
						<!-- duration day/s (decorated server-side in OutstationController::index) -->
						<td @if($outstation->remarks) data-bs-toggle="tooltip" data-bs-custom-class="custom-tooltip" data-bs-title="{{ $outstation->remarks }}" @endif>
							{{ Str::limit($outstation->remarks, 7, ' >') }}
						</td>
						<td>
							<a href="{{ route('outstation.edit', $outstation->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-regular fa-pen-to-square"></i></a>
							<button type="button" id="out" class="btn btn-sm btn-outline-secondary text-danger delete_button" data-id="{{ $outstation->id }}"><i class="fa-regular fa-trash-can"></i></button>
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
		outstation: '{{ url('outstation') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection
