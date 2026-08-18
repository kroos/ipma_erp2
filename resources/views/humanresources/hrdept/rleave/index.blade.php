@extends('layouts.app')

@section('content')
<div class="page-humanresources-hrdept-rleave-index container row align-items-start justify-content-center">
	@include('humanresources.hrdept.navhr')
	<h4>Replacement Leave&nbsp; <a class="btn btn-sm btn-outline-secondary" href="{{ route('rleave.create') }}"><i class="fa-solid fa-person-walking-arrow-loop-left fa-beat"></i> Add Replacement Leave</a></h4>
	<div class="col-sm-12 row table-responsive">
		<table id="replacement" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Date Start</th>
					<th>Date End</th>
					<th>Customer</th>
					<th>Reason</th>
					<th>Total</th>
					<th>Utilize</th>
					<th>Balance</th>
					<th>Replacement Leave</th>
					<th>Remarks</th>
					<th>Edit</th>
					<th>Cancel</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($replacements as $replacement)
				<tr>
					<td>{{ $replacement->username }}</td>
					<td class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $replacement->belongstostaff?->name }}">{{ $replacement->belongstostaff?->name }}</td>
					<td>{{ $replacement->date_start_fmt }}</td>
					<td>{{ $replacement->date_end_fmt }}</td>
					<td class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $replacement->belongstocustomer?->customer }}">{{ Str::limit($replacement->belongstocustomer?->customer, 10, '>') }}</td>
					<td class="text-truncate" style="max-width: 150px;" data-toggle="tooltip" title="{{ $replacement->reason }}">{{ Str::limit($replacement->reason, 10, '>') }}</td>
					<td class="text-center">{{ $replacement->leave_total }}</td>
					<td class="text-center">{{ $replacement->leave_utilize }}</td>
					<td class="text-center">{{ $replacement->leave_balance }}</td>
					<td class="text-center">
						{!! $replacement->leave_refs->implode('') !!}
					</td>
					<td class="text-truncate" style="max-width: 100px;" data-toggle="tooltip" title="{{ $replacement->remarks }}">{{ Str::limit($replacement->remarks, 10, '>') }}</td>
					<td class="text-center">
						<a href="{{ route('rleave.edit', $replacement->id) }}" class="btn btn-sm btn-outline-secondary">
							<i class="fa-regular fa-pen-to-square"></i>
						</a>
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-outline-secondary delete_replacement" data-id="{{ $replacement->id }}" data-table="replacement" >
							<i class="fa-regular fa-trash-can"></i>
						</button>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>

		<div class="d-flex justify-content-center">
			{{ $replacements->links() }}
		</div>
	</div>
</div>
@endsection


@section('js')
window.data = {
	url: {
		base: '{{ url('rleave') }}',
	},
};
@endsection


@section('nonjquery')

@endsection
