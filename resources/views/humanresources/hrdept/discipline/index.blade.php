@extends('layouts.app')

@section('content')
<div class="container">
	@include('humanresources.hrdept.navhr')
	<div class="row mt-3">
		<div class="col-md-2">
			<h4>Staff Discipline</h4>
		</div>
		<div class="col-md-10">
			<a href="{{ route('discipline.create') }}" class="btn btn-sm btn-outline-secondary">
				<i class="fa-solid fa-handcuffs fa-beat"></i> Create Disciplinary Action For Staff
			</a>
		</div>
	</div>
	<div>
		@if($disciplinary->count())
		<table id="discipline" class="table table-hover table-sm align-middle" style="font-size:12px">
			<thead>
				<tr>
					<th class="text-center" style="max-width: 30px;">ID</th>
					<th style="max-width: 120px;">Name</th>
					<th class="text-center" style="max-width: 55px;">Misonduct<br />Date</th>
					<th class="text-center" style="max-width: 55px;">Created<br />Date</th>
					<th class="text-center" style="max-width: 80px;">Department</th>
					<th style="max-width: 110px;">Disciplinary Action</th>
					<th style="max-width: 200px;">Violation</th>
					<th>Reason</th>
					<th class="text-center" style="max-width: 55px;">Softcopy</th>
					<th class="text-center" style="max-width: 40px;">Edit</th>
					<th class="text-center" style="max-width: 40px;">Cancel</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($disciplinary as $discipline)
				<tr>
					<td class="text-center">
						<a href="{{ route('discipline.show', $discipline->id) }}">
							{{ App\Models\Login::where([['staff_id', $discipline->staff_id], ['active', 1]])->first()?->username }}
						</a>
					</td>
					<td class="text-truncate" style="max-width: 120px;" data-toggle="tooltip" title="{{ $discipline->belongstostaff?->name }}">
						{{ $discipline->belongstostaff?->name }}
					</td>
					<td class="text-center">
						{{ \Carbon\Carbon::parse($discipline->misconduct_date)->format('j M Y') }}
					</td><td class="text-center">
						{{ \Carbon\Carbon::parse($discipline->action_taken_date)->format('j M Y') }}
					</td>
					<td class="text-center">
						{{ $discipline->belongstostaff?->belongstomanydepartment()?->wherePivot('main', 1)->first()->code }}
					</td>
					<td class="text-truncate" style="max-width: 110px;" data-toggle="tooltip" title="{{ $discipline->belongstooptdisciplinaryaction->disciplinary_action }}">
						{{ $discipline->belongstooptdisciplinaryaction->disciplinary_action }}
					</td>
					<td class="text-truncate" style="max-width: 200px;" data-toggle="tooltip" title="{{ $discipline->belongstooptviolation->violation }}">
						{{ $discipline->belongstooptviolation->violation }}
					</td>
					<td class="text-truncate" style="max-width: 1px;" data-toggle="tooltip" title="{{ $discipline->reason }}">
						{{ $discipline->reason }}
					</td>
					<td class="text-center">
						@if ($discipline->softcopy)
						<a href="{{ asset('storage/disciplinary/' . $discipline->softcopy) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
							<i class="bi bi-file-text" style="font-size: 15px;"></i>
						</a>
						@endif
					</td>
					<td class="text-center">
						<a href="{{ route('discipline.edit', $discipline->id) }}" class="btn btn-sm btn-outline-secondary">
							<i class="bi bi-pencil-square" style="font-size: 15px;"></i>
						</a>
					</td>
					<td class="text-center">
						<button type="button" class="btn btn-sm btn-outline-secondary delete_discipline" data-id="{{ $discipline->id }}" data-softcopy="{{ $discipline->softcopy }}" data-table="discipline">
							<i class="fa-regular fa-trash-can"></i>
						</button>
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		@endif
	</div>
</div>
@endsection


@section('js')
window.data = {
	route: {
	},
	url: {
		discipline: '{{ url('discipline') }}',
	},
	old: {
	},
	errors: @json($errors->toArray()),
};
@endsection


@section('nonjquery')

@endsection
